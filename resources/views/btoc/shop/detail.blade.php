@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">
    <a href="{{ route('btoc.shop.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
        ← 戻る (Quay lại)
    </a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            ショップ詳細 <span class="block text-sm text-gray-500 font-normal mt-1">Chi tiết Shop</span>
        </h1>
        <a href="{{ route('btoc.shop.edit', $shop->id) }}" class="px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition">
            編集する (Chỉnh sửa)
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm max-w-2xl">
        <dl class="divide-y divide-gray-100">
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">Mã Shop (Shop Code)</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $shop->shop_code }}</dd>
            </div>
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">Tên Shop (Shop Name)</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $shop->shop_name }}</dd>
            </div>
            {{-- <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">Client ID</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $shop->client_id ?? 'N/A' }}</dd>
            </div>
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">Trạng thái Token</dt>
                <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                    @if($shop->access_token)
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Đã kết nối</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Chưa kết nối</span>
                    @endif
                </dd>
            </div> --}}
        </dl>
    </div>
</div>
@endsection