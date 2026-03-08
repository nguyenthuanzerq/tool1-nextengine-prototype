<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopController extends Controller
{
    /**
     * Hiển thị danh sách shop (Có phân trang)
     */
    public function index()
    {
        $shops = Shop::orderBy('id', 'desc')->paginate(10);
        
        return view('btoc.shops', compact('shops'));
    }

    /**
     * Hiển thị form thêm mới shop
     */
    public function create()
    {
        $isCreate = true;
        $shop = new Shop(); 
        
        return view('btoc.shop_detail', compact('isCreate', 'shop'));
    }

    /**
     * Xử lý lưu shop mới vào Database (Store)
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'shop_code'     => 'required|string|max:255|unique:shops,shop_code',
            'shop_name'     => 'required|string|max:255',
            'client_id'     => 'required|string|max:255',
            'client_secret' => 'nullable|string',
        ], [
            'shop_code.required' => '店舗コードを空白のままにすることはできません。',
            'shop_code.unique'   => 'このショップコードは既に存在します。',
            'shop_name.required' => 'ショップ名は空欄にできません。',
            'client_id.required' => 'クライアント ID を空白のままにすることはできません。'
        ]);

        // Lưu vào database
        Shop::create($validated);

        return redirect()->route('btoc.shops')->with('success', 'Đã thêm shop mới thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa shop
     */
    public function edit($id)
    {
        $isCreate = false;
        $shop = Shop::findOrFail($id); 
        
        return view('btoc.shop_detail', compact('isCreate', 'shop'));
    }

    /**
     * Xử lý cập nhật thông tin shop (Update)
     */
    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $validated = $request->validate([
            'shop_name'     => 'required|string|max:255',
            'client_id'     => 'required|string|max:255',
            'client_secret' => 'nullable|string',
        ]);

        $shop->update($validated);

        return redirect()->route('btoc.shop.edit', $id)->with('success', 'Đã cập nhật thông tin shop.');
    }

    /**
     * Xóa shop (Destroy)
     */
    public function destroy($id)
    {
        $shop = Shop::findOrFail($id);
        
        // Bạn có thể thêm logic xóa đơn hàng, tồn kho liên quan ở đây nếu cần thiết
        // Order::where('shop_id', $shop->id)->delete();
        
        $shop->delete();

        return redirect()->route('btoc.shops')->with('success', 'Đã xóa shop thành công.');
    }

    /**
     * Điều hướng sang NextEngine để xác thực (OAuth2)
     */
    public function reAuthorize($id)
    {
        $shop = Shop::findOrFail($id);

        // Lưu ID của shop đang xác thực vào Session để dùng ở bước Callback
        session(['oauth_target_shop_id' => $shop->id]);

        // Tạo URL đăng nhập NextEngine
        $query = http_build_query([
            'client_id'     => $shop->client_id,
            'redirect_uri'  => route('btoc.nextengine.callback'),
            'response_type' => 'code',
        ]);

        return redirect("https://api.next-engine.org/oauth2/authorize?" . $query);
    }

    /**
     * Nhận mã Code từ NextEngine và đổi lấy Access Token
     */
    public function callback(Request $request)
    {
        $shopId = session('oauth_target_shop_id');
        
        if (!$shopId) {
            return redirect()->route('btoc.shops')->with('error', 'Lỗi phiên xác thực. Vui lòng thử lại.');
        }

        $shop = Shop::findOrFail($shopId);
        $code = $request->query('code');

        if (!$code) {
            return redirect()->route('btoc.shop.edit', $shopId)->with('error', 'Người dùng từ chối xác thực hoặc lỗi mã Code.');
        }

        // Đổi code lấy Access Token qua API của NextEngine
        $response = Http::asForm()->post('https://api.next-engine.org/oauth2/token', [
            'client_id'     => $shop->client_id,
            'client_secret' => $shop->client_secret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => route('btoc.nextengine.callback'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Cập nhật Token vào Database
            $shop->update([
                'access_token'     => $data['access_token'],
                'refresh_token'    => $data['refresh_token'] ?? null,
                'token_expires_at' => Carbon::now()->addSeconds($data['expires_in'] ?? 3600), // Thời gian hết hạn
            ]);

            session()->forget('oauth_target_shop_id'); // Xóa session sau khi dùng xong

            return redirect()->route('btoc.shop.edit', $shop->id)->with('success', 'Kết nối NextEngine và lấy Token thành công!');
        }

        // Ghi log nếu có lỗi từ NextEngine API
        Log::error('NEXTENGINE_AUTH_ERROR', ['response' => $response->body()]);
        return redirect()->route('btoc.shop.edit', $shop->id)->with('error', 'Lỗi khi lấy Access Token từ NextEngine. Vui lòng kiểm tra lại Client Secret.');
    }

    /**
     * Test kết nối API tới NextEngine
     */
    public function testConnection($id)
    {
        $shop = Shop::findOrFail($id);

        // 1. Kiểm tra xem shop đã có token chưa
        if (empty($shop->access_token)) {
            return redirect()->route('btoc.shop.edit', $id)
                ->with('error', 'Chưa có Access Token. Vui lòng nhấn "Re-authorize" để cấp quyền trước.');
        }

        try {
            // 2. Gọi thử một API đơn giản của NextEngine (ví dụ lấy thông tin user) để test token
            $response = Http::asForm()->post('https://api.next-engine.org/api_v1_login_user/info', [
                'access_token' => $shop->access_token
            ]);

            // 3. Xử lý kết quả trả về
            if ($response->successful() && $response->json('result') === 'success') {
                return redirect()->route('btoc.shop.edit', $id)
                    ->with('success', 'Kết nối thành công! Token hoàn toàn hợp lệ.');
            }

            // Nếu thất bại (Token hết hạn hoặc sai)
            Log::error('NEXTENGINE_TEST_CONN_FAILED', [
                'shop_id' => $id,
                'response' => $response->body()
            ]);

            return redirect()->route('btoc.shop.edit', $id)
                ->with('error', 'Kết nối thất bại. Token có thể đã hết hạn hoặc bị thu hồi. Vui lòng nhấn "Re-authorize".');

        } catch (\Exception $e) {
            Log::error('NEXTENGINE_TEST_CONN_EXCEPTION', ['message' => $e->getMessage()]);
            return redirect()->route('btoc.shop.edit', $id)
                ->with('error', 'Lỗi hệ thống khi gọi API: ' . $e->getMessage());
        }
    }
}