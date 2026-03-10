<?php 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm() {
        return view('auth.login'); 
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $oldSessionId = $request->session()->getId();

        $remember = $request->has('remember'); 
        if (Auth::attempt($credentials, $remember)) { // Hàm attempt đã tự động tạo một session mới để ngăn chặn session fixation
            // $request->session()->regenerate(); // Tạo một session mới để ngăn chặn session fixation (tấn công chiếm đoạt session)

            $newSessionId = $request->session()->getId(); 
            // dd([
            //     'Tình trạng' => 'Đăng nhập THÀNH CÔNG',
            //     'Session ID CŨ (Trước đăng nhập)' => $oldSessionId,
            //     'Session ID MỚI (Sau đăng nhập)' => $newSessionId,
            //     'Kết luận' => $oldSessionId === $newSessionId ? ' Old Session' : 'New Sesssion'
            // ]);

            return redirect()->intended('/btoc/dashboard'); // Hàm intended sẽ chuyển hướng người dùng đến trang họ muốn truy cập trước khi bị yêu cầu đăng nhập, nếu không có thì sẽ chuyển đến /btoc/dashboard
        }

        return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ]);
    }

    public function logout(Request $request) {
        Auth::logout(); // hàm logout sẽ xóa thông tin đăng nhập của người dùng khỏi session, nhưng session vẫn còn tồn tại với ID cũ
        // $request->session()->invalidate(); // Xóa session hiện tại
        // $request->session()->regenerateToken(); // Tạo một token mới để ngăn chặn CSRF sau khi đăng xuất
        return redirect('/login');
    }
}