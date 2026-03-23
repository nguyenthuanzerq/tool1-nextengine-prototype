@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            プラットフォーム管理
            <span class="block text-sm text-gray-500 font-normal mt-1">Quản lý Nền tảng EC</span>
        </h1>
        <a href="{{ route('btoc.platforms.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition shadow-sm">
            + 新規追加 (Thêm mới)
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm">
                    <th class="px-6 py-4 font-medium text-gray-700">ID</th>
                    <th class="px-6 py-4 font-medium text-gray-700">コード (Code)</th>
                    <th class="px-6 py-4 font-medium text-gray-700">名前 (Name)</th>
                    <th class="px-6 py-4 font-medium text-gray-700">認証タイプ (Auth Type)</th>
                    <th class="px-6 py-4 font-medium text-gray-700 text-center">ショップ数</th>
                    <th class="px-6 py-4 font-medium text-gray-700 text-right">アクション</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($platforms as $platform)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $platform->id }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $platform->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $platform->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($platform->auth_type === 'oauth2')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OAuth2</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">API Key</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 text-center">{{ $platform->shops_count }}</td>
                        <td class="px-6 py-4 text-sm text-right space-x-3">
                            <a href="{{ route('btoc.platforms.edit', $platform->id) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium">編集 (Sửa)</a>
                            <form action="{{ route('btoc.platforms.destroy', $platform->id) }}"
                                  method="POST"
                                  class="inline-block"
                                  onsubmit="return confirm('このプラットフォームを削除しますか？(Bạn có chắc chắn muốn xóa?)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">削除 (Xóa)</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            プラットフォームがまだ登録されていません。(Chưa có nền tảng nào)
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">{{ $platforms->links() }}</div>
    </div>
</div>
@endsection
