<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\EcPlatform;
use Illuminate\Http\Request;

class EcPlatformController extends Controller
{
    public function index()
    {
        $platforms = EcPlatform::orderBy('id', 'desc')->paginate(10);
        return view('btoc.ec-platforms.index', compact('platforms'));
    }

    public function create()
    {
        $platform = new EcPlatform();
        return view('btoc.ec-platforms.save', compact('platform'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:ec_platforms',
            'name' => 'required|string|max:255',
            'auth_type' => 'required|string|in:oauth2,api_key,basic',
            'website_url' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        EcPlatform::create($validated);
        return redirect()->route('btoc.ec-platforms.index')->with('success', 'プラットフォームが追加されました。(Đã thêm nền tảng mới)');
    }

    public function edit(EcPlatform $ec_platform)
    {
        return view('btoc.ec-platforms.save', ['platform' => $ec_platform]);
    }

    public function update(Request $request, EcPlatform $ec_platform)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:ec_platforms,code,' . $ec_platform->id,
            'name' => 'required|string|max:255',
            'auth_type' => 'required|string|in:oauth2,api_key,basic',
            'website_url' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        $ec_platform->update($validated);
        return redirect()->route('btoc.ec-platforms.index')->with('success', 'プラットフォームが更新されました。(Đã cập nhật nền tảng)');
    }

    public function destroy(EcPlatform $ec_platform)
    {
        $ec_platform->delete();
        return redirect()->route('btoc.ec-platforms.index')->with('success', '削除されました。(Đã xóa nền tảng)');
    }
}   