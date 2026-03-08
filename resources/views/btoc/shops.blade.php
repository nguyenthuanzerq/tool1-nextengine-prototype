@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    {{-- Hiển thị thông báo thành công hoặc lỗi --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            ショップ管理
            <span class="block text-sm text-gray-500 font-normal mt-1">
                Quản lý Shop
            </span>
        </h1>
        
        {{-- Nút Thêm Shop --}}
        <a href="{{ route('btoc.shop.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
            + 新規追加 (Thêm mới)
        </a>
    </div>

    {{-- Bảng danh sách --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm">
                    <th class="px-6 py-4 font-medium text-gray-700">ID</th>
                    <th class="px-6 py-4 font-medium text-gray-700">ショップ名 </th>
                    <th class="px-6 py-4 font-medium text-gray-700">Client ID</th>
                    <th class="px-6 py-4 font-medium text-gray-700">更新日時 </th>
                    <th class="px-6 py-4 font-medium text-gray-700 text-right">アクション </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($shops as $shop)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $shop->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $shop->shop_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $shop->client_id ?? 'Chưa cấu hình' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $shop->updated_at ? $shop->updated_at->format('Y-m-d H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right space-x-3">
                            {{-- Nút Sửa --}}
                            <a href="{{ route('btoc.shop.edit', $shop->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                編集 
                            </a>

                            {{-- Form Xóa --}}
                            <form action="{{ route('btoc.shop.destroy', $shop->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa shop này không? Toàn bộ dữ liệu liên quan có thể bị ảnh hưởng.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                    削除 
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            ショップデータはまだありません。新しいショップを追加してください。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Phân trang --}}
        <div class="p-4 border-t border-gray-200">
            {{ $shops->links() }}
        </div>
    </div>
</div>
@endsection