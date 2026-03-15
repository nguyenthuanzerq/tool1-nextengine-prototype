<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-w-[600px] margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .header {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #0056b3;
        }

        .content {
            padding: 20px 0;
        }

        .order-info {
            background: #f4f4f4;
            padding: 15px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>ご注文ありがとうございます</h2>
        </div>
        <div class="content">
            <p>{{ $order->purchaser_name }} 様</p>
            <p>この度は、<strong>{{ $order->shop->shop_name ?? '当店' }}</strong>をご利用いただき、誠にありがとうございます。</p>
            <p>以下の内容でご注文を承りましたので、ご確認ください。</p>

            <div class="order-info">
                <h3>【ご注文内容】</h3>
                <p><strong>受注番号 (Mã ĐH):</strong> {{ $order->receipt_receipt_id ?? $order->id }}</p>
                <p><strong>ご注文日時 (Ngày đặt):</strong>
                    {{ $order->receive_order_date ?? $order->created_at->format('Y/m/d H:i') }}</p>
                <p><strong>お支払い合計 (Tổng tiền):</strong> ¥{{ number_format($order->receive_order_total_amount) }}</p>

                <h4>商品リスト:</h4>
                <ul>
                    @foreach ($order->products ?? [] as $item)
                        <li>{{ $item->product_name }} - {{ $item->qty }}点</li>
                    @endforeach
                </ul>
            </div>

            <p>商品の発送準備が整い次第、改めて発送完了メールをお送りいたします。<br>今しばらくお待ちくださいませ。</p>
        </div>
        <div class="footer">
            <p>※本メールは送信専用システムから自動配信されています。</p>
            <p>{{ $order->shop->shop_name ?? 'NextEngine Shop' }}</p>
        </div>
    </div>
</body>

</html>
