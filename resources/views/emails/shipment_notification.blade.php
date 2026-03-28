<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>出荷通知</title>
</head>
<body style="font-family: sans-serif; font-size: 14px; color: #333; line-height: 1.6;">
    <p>出荷のお知らせです。</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 500px;">
        <tr style="background:#f5f5f5;">
            <td style="border:1px solid #ddd; font-weight:bold; width:140px;">注文番号</td>
            <td style="border:1px solid #ddd;">{{ $order->platform_order_id ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #ddd; font-weight:bold;">ショップ</td>
            <td style="border:1px solid #ddd;">{{ $order->shop->shop_name ?? '-' }}</td>
        </tr>
        <tr style="background:#f5f5f5;">
            <td style="border:1px solid #ddd; font-weight:bold;">購入者名</td>
            <td style="border:1px solid #ddd;">{{ $order->buyer_name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #ddd; font-weight:bold;">受注日</td>
            <td style="border:1px solid #ddd;">{{ $order->ordered_at?->format('Y-m-d') ?? '-' }}</td>
        </tr>
        <tr style="background:#f5f5f5;">
            <td style="border:1px solid #ddd; font-weight:bold;">追跡番号</td>
            <td style="border:1px solid #ddd;"><strong>{{ $order->tracking_number }}</strong></td>
        </tr>
        <tr>
            <td style="border:1px solid #ddd; font-weight:bold;">配送方法</td>
            <td style="border:1px solid #ddd;">{{ $order->delivery_method ?? '-' }}</td>
        </tr>
        <tr style="background:#f5f5f5;">
            <td style="border:1px solid #ddd; font-weight:bold;">合計金額</td>
            <td style="border:1px solid #ddd;">¥{{ number_format($order->total_amount ?? 0) }}</td>
        </tr>
    </table>

    <p style="margin-top: 24px; color: #888; font-size: 12px;">
        このメールはシステムから自動送信されています。
    </p>
</body>
</html>
