@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-[1440px] mx-auto">

        <h1 class="text-2xl font-semibold text-gray-900 mb-6">
            在庫・出庫
            <span class="block text-sm text-gray-500 font-normal mt-1">
                Tồn kho & Xuất hàng
            </span>
        </h1>

        {{-- Inventory Section --}}
        <div class="bg-white rounded-lg border border-gray-200 mb-6">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    在庫一覧
                    <span class="block text-sm text-gray-500 font-normal mt-0.5">
                        Danh sách tồn kho
                    </span>
                </h2>

                <form method="POST" action="#">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        リアルタイム更新
                        <span class="text-xs opacity-90">(Cập nhật thời gian thực)</span>
                    </button>
                </form>
            </div>

            {{-- ===== FILTER (SỬA TỪ ĐÂY) ===== --}}
            <form method="GET" action="#"
                class="px-6 py-4 border-b border-gray-200 bg-gray-50">

                <div class="flex flex-wrap gap-4 items-end">

                    {{-- Shop Dropdown --}}
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">
                            ショップ名
                        </label>
                        <select name="shop_id" class="border rounded px-3 py-2 text-sm min-w-[200px]">
                            <option value="">すべてのショップ</option>
                            {{-- @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}"
                                    {{ (string) request('shop_id') === (string) $shop->id ? 'selected' : '' }}>
                                    {{ $shop->shop_name }}
                                </option>
                            @endforeach --}}
                        </select>
                    </div>

                    {{-- Mall Dropdown --}}
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">
                            モール名
                        </label>
                        <select name="mall_id" class="border rounded px-3 py-2 text-sm min-w-[200px]">
                            <option value="">すべてのモール</option>
                            <option value="">Shopi</option>
                            <option value="">Lazada</option>
                            {{-- @foreach ($malls as $mall)
                                <option value="{{ $mall->id }}"
                                    {{ (string) request('mall_id') === (string) $mall->id ? 'selected' : '' }}>
                                    {{ $mall->mall_name }}
                                </option>
                            @endforeach --}}
                        </select>
                    </div>

                    {{-- Product Code --}}
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">
                            商品コード
                        </label>
                        <input type="text" name="product_code" value="#"
                            class="border rounded px-3 py-2 text-sm min-w-[200px]" placeholder="PRD-001">
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">
                            検索
                        </button>

                        <a href="{{ route('btoc.inventory.index') }}" class="px-4 py-2 bg-gray-300 rounded text-sm">
                            クリア
                        </a>
                    </div>

                </div>
            </form>
            {{-- ===== END FILTER ===== --}}

            {{-- ===================================================== --}}
            {{-- 🔒 今後の運用イメージのすり合わせ（事前確認）※番号付き --}}
            {{-- ===================================================== --}}
            {{-- <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm text-gray-700">
                <div class="font-semibold mb-2">
                    🔒 今後の運用イメージのすり合わせ（事前確認）
                </div>

                <div class="mb-3 text-gray-600 leading-relaxed">
                    今後の拡張や安定運用を見据えて、方向性だけ事前に共有させてください。<br>
                    現時点での想定をお聞かせいただけますと助かります。
                </div> --}}

                {{-- チャネル --}}
                {{-- <div class="font-semibold mb-2">【チャネルについて】</div>
                <ul class="list-disc ml-5 space-y-2 mb-4">
                    <li>① 新しい販売チャネル（例：Yahoo、楽天など）は、今後追加できる形にしますか？</li>
                    <li>② チャネルごとに接続のための情報（IDなど）は必要になりますか？</li>
                    <li>③ 現在使用していないチャネルは、画面上に表示しない形にしますか？</li>
                </ul> --}}

                {{-- 出品 --}}
                {{-- <div class="font-semibold mb-2">【出品の考え方について】</div>
                <ul class="list-disc ml-5 space-y-2 mb-4">
                    <li>
                        ④ 商品はモールごとに別管理でしょうか？<br>
                        それとも、どのチャネルでも同じ商品として扱いますか？
                    </li>
                    <li>⑤ 特に設定をしない場合、自動的に出品される想定でしょうか？</li>
                    <li>
                        ⑥ 出品の設定は商品ごとに行いますか？<br>
                        それともShopやモール単位でまとめて設定しますか？
                    </li>
                    <li>⑦ 「特定のチャネルだけに出す」といった設定は想定されていますか？</li>
                    <li>⑧ 自動更新の際にも、この出品の設定は反映される想定でしょうか？</li>
                </ul> --}}

                {{-- 運用 --}}
                {{-- <div class="font-semibold mb-2">【運用面について】</div>
                <ul class="list-disc ml-5 space-y-2">
                    <li>⑨ 出品設定の変更履歴は残す形にしますか？</li>
                    <li>⑩ 一括で出品を停止する場合など、確認画面を設けた方がよろしいでしょうか？</li>
                </ul>
            </div> --}}
            <div class="overflow-x-auto">
                <table class="w-full table-fixed">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 w-[180px]">
                                ショップ名
                                <span class="block text-gray-500 font-normal">Tên shop</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                商品コード
                                <span class="block text-gray-500 font-normal">Mã sản phẩm</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                商品名
                                <span class="block text-gray-500 font-normal">Tên sản phẩm</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                在庫数
                                <span class="block text-gray-500 font-normal">Số lượng tồn</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                出庫可能数
                                <span class="block text-gray-500 font-normal">Số có thể xuất</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                最終更新日時
                                <span class="block text-gray-500 font-normal">Cập nhật cuối</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        {{-- @foreach ($inventoryData as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 w-[180px] whitespace-nowrap">
                                    {{ $item->mall->shop->shop_name ?? $item->shop_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->product_code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->product_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->stock }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->available_stock }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->last_updated }}
                                </td>
                            </tr>
                        @endforeach --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Shipment Section giữ nguyên --}}
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    出庫状況
                    <span class="block text-sm text-gray-500 font-normal mt-0.5">
                        Tình trạng xuất hàng
                    </span>
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                注文ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                出庫状態
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                出庫日
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                運送会社
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                                送り状番号
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        {{-- @foreach ($shipmentData as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->order_id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->shipment_status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->shipment_date }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->carrier }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->tracking_number }}</td>
                            </tr>
                        @endforeach --}}
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
