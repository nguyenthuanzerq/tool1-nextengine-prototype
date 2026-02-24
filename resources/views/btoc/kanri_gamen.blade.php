@extends('layouts.app')

@section('content')

<div class="max-w-[1800px] mx-auto p-8">

    <div class="text-lg font-semibold mb-1">BtoC NextEngine</div>
    <h1 class="text-2xl font-bold mb-1">BtoC NextEngine 管理画面</h1>

    <div class="text-sm text-gray-700 mb-6">
        🔒 <span class="font-semibold">Phase1:</span> Order list + tracking input + mail trigger
    </div>

<div class="border border-gray-300">
    <table class="w-full border-collapse text-sm">

        <thead class="bg-gray-100">
        <tr>

            <th class="border px-3 py-4 text-left align-top">
                ショップ名<br><span class="text-xs text-gray-600">(Tên Shop)</span>
            </th>

            <th class="border px-3 py-4 text-left align-top">
                ShopID
            </th>

            <th class="border px-3 py-4 text-left align-top">
                注文ID<br><span class="text-xs text-gray-600">(ID đơn hàng)</span>
            </th>

            <th class="border px-3 py-4 text-left align-top">
                商品名<br><span class="text-xs text-gray-600">(Tên sản phẩm)</span>
            </th>

            <th class="border px-3 py-4 text-left align-top">
                配送会社<br><span class="text-xs text-gray-600">(Công ty vận chuyển)</span>
            </th>

            <th class="border px-3 py-4 text-left align-top">
                発送番号（入力）
                <div class="text-xs text-gray-500">🔒 TBD: validation rule</div>
            </th>

            <th class="border px-3 py-4 text-left align-top">
                注文者ID
            </th>

            <th class="border px-3 py-4 text-left align-top">
                注文者名
            </th>

            <th class="border px-3 py-4 text-left align-top">
                配送先住所
            </th>

            <th class="border px-3 py-4 text-left align-top">
                電話番号
            </th>

            <th class="border px-3 py-4 text-left align-top">
                メールアドレス
            </th>

            <th class="border px-3 py-4 text-center align-top">
                操作
            </th>

        </tr>
        </thead>

       <tbody>
<tr class="hover:bg-gray-50">

    <td class="border px-3 py-3 break-words">
        ファッションストアA
    </td>

    <td class="border px-3 py-3">
        SHOP-001
    </td>

    <td class="border px-3 py-3">
        ORD-20260210-001
    </td>

    <td class="border px-3 py-3 break-words">
        カジュアルTシャツ（ホワイト・Mサイズ）
    </td>

    <td class="border px-3 py-3">
        ヤマト運輸
    </td>

    <td class="border px-3 py-3">
        <input
            type="text"
            name="tracking_number"
            form="trackingForm1"
            class="w-full px-2 py-1 border border-gray-300 focus:outline-none focus:border-gray-500"
            placeholder="発送番号を入力"
        />
    </td>

    <td class="border px-3 py-3">
        CUST-4521
    </td>

    <td class="border px-3 py-3">
        田中 太郎
    </td>

    <td class="border px-3 py-3 break-words">
        東京都渋谷区神南1-2-3 渋谷マンション101
    </td>

    <td class="border px-3 py-3">
        090-1234-5678
    </td>

    <td class="border px-3 py-3 break-words">
        tanaka.taro@example.com
    </td>

    <td class="border px-3 py-3 text-center">

        <form id="trackingForm1" method="POST" action="{{ route('btoc.registerTracking') }}">
            @csrf
            <input type="hidden" name="order_id" value="1">

            <button
                type="submit"
                class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 transition-colors leading-tight"
            >
                登録<br>
                <span class="text-xs">(Đăng ký)</span>
            </button>
        </form>

    </td>

</tr>
</tbody>

    </table>
</div>

    <div class="mt-6 border border-gray-300 bg-gray-50 p-4 text-sm">
        <p class="text-gray-800">
            発送番号を入力して「登録」を押すと、購入者へ発送完了メールが自動送信されます。
        </p>
        <p class="text-xs text-gray-600 mt-1">
            (Chỉ cần nhập mã vận đơn và nhấn Đăng ký, hệ thống sẽ tự động gửi email thông báo giao hàng cho người mua.)
        </p>
    </div>

    <div class="mt-6 border border-gray-300 bg-gray-50 p-4">
        <div class="text-sm font-semibold mb-2">🔒 Phase2 TBD:</div>
        <ul class="list-disc pl-6 text-sm text-gray-800 space-y-1">
            <li>Realtime inventory</li>
            <li>Auto sync method (cron / realtime)</li>
            <li>Mail template detail</li>
        </ul>
    </div>

</div>

@endsection