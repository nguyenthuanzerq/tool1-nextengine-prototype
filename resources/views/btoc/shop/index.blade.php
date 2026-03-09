@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            ショップ管理 <span class="block text-sm text-gray-500 font-normal mt-1">Quản lý Shop</span>
        </h1>
        <a href="{{ route('btoc.shop.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
            + 新規追加 (Thêm mới)
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm">
                    <th class="px-6 py-4 font-medium text-gray-700">ID</th>
                    <th class="px-6 py-4 font-medium text-gray-700">ショップ名</th>
                    {{-- <th class="px-6 py-4 font-medium text-gray-700">Client ID</th> --}}
                    <th class="px-6 py-4 font-medium text-gray-700 text-right">アクション</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($shops as $shop)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $shop->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $shop->shop_name }}</td>
                        {{-- <td class="px-6 py-4 text-sm text-gray-600">{{ $shop->client_id ?? 'Chưa cấu hình' }}</td> --}}
                        <td class="px-6 py-4 text-sm text-right space-x-3">
                            <a href="{{ route('btoc.shop.show', $shop->id) }}" class="text-gray-600 hover:text-gray-900 font-medium">詳細 (Xem)</a>
                            <a href="{{ route('btoc.shop.edit', $shop->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">編集 (Sửa)</a>
                            <form action="{{ route('btoc.shop.destroy', $shop->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa shop này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">削除 (Xóa)</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Chưa có dữ liệu shop.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">{{ $shops->links() }}</div>
    </div>
</div>
@endsection