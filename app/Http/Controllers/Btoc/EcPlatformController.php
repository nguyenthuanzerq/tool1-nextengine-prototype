<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\EcPlatform;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EcPlatformController extends Controller
{
    /**
     * Display a listing of EC platforms.
     */
    public function index()
    {
        $platforms = EcPlatform::withCount('shops')
            ->orderBy('id', 'asc')
            ->paginate(10);

        return view('btoc.ec_platforms.index', compact('platforms'));
    }

    /**
     * Show the form for creating a new platform.
     */
    public function create()
    {
        return view('btoc.ec_platforms.save', [
            'isCreate' => true,
            'platform' => new EcPlatform(),
        ]);
    }

    /**
     * Store a newly created platform.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string|max:50|unique:ec_platforms,code',
            'name'      => 'required|string|max:255',
            'auth_type' => 'required|in:oauth2,api_key',
        ], [
            'code.required'      => 'プラットフォームコードは必須です。(Mã nền tảng là bắt buộc)',
            'code.unique'        => 'このコードは既に使用されています。(Mã này đã được sử dụng)',
            'name.required'      => 'プラットフォーム名は必須です。(Tên nền tảng là bắt buộc)',
            'auth_type.required' => '認証タイプは必須です。(Loại xác thực là bắt buộc)',
            'auth_type.in'       => '認証タイプは oauth2 または api_key でなければなりません。(Loại xác thực phải là oauth2 hoặc api_key)',
        ]);

        EcPlatform::create($validated);

        return redirect()
            ->route('btoc.platforms.index')
            ->with('success', '新しいプラットフォームが正常に追加されました。(Thêm nền tảng thành công)');
    }

    /**
     * Show the form for editing the specified platform.
     */
    public function edit($id)
    {
        $platform = EcPlatform::findOrFail($id);

        return view('btoc.ec_platforms.save', [
            'isCreate' => false,
            'platform' => $platform,
        ]);
    }

    /**
     * Update the specified platform.
     */
    public function update(Request $request, $id)
    {
        $platform = EcPlatform::findOrFail($id);

        $validated = $request->validate([
            'code'      => ['required', 'string', 'max:50', Rule::unique('ec_platforms', 'code')->ignore($platform->id)],
            'name'      => 'required|string|max:255',
            'auth_type' => 'required|in:oauth2,api_key',
        ], [
            'code.required'      => 'プラットフォームコードは必須です。(Mã nền tảng là bắt buộc)',
            'code.unique'        => 'このコードは既に使用されています。(Mã này đã được sử dụng)',
            'name.required'      => 'プラットフォーム名は必須です。(Tên nền tảng là bắt buộc)',
            'auth_type.required' => '認証タイプは必須です。(Loại xác thực là bắt buộc)',
            'auth_type.in'       => '認証タイプは oauth2 または api_key でなければなりません。(Loại xác thực phải là oauth2 hoặc api_key)',
        ]);

        $platform->update($validated);

        return redirect()
            ->route('btoc.platforms.index')
            ->with('success', 'プラットフォームが正常に更新されました。(Cập nhật nền tảng thành công)');
    }

    /**
     * Remove the specified platform (only if no shops are linked).
     */
    public function destroy($id)
    {
        $platform = EcPlatform::findOrFail($id);

        if ($platform->shops()->count() > 0) {
            return redirect()
                ->route('btoc.platforms.index')
                ->with('error', "プラットフォーム「{$platform->name}」にはショップが紐づいているため削除できません。(Không thể xóa vì có Shop đang liên kết)");
        }

        $platform->delete();

        return redirect()
            ->route('btoc.platforms.index')
            ->with('success', 'プラットフォームが正常に削除されました。(Xóa nền tảng thành công)');
    }
}
