<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailSettingController extends Controller
{
   // emailSettings
public function emailSettings(Request $request)
{
    $templateName = "発送通知メール";

    $subject = "【{carrier}】ご注文商品を発送しました - 送り状番号: {tracking_number}";

    $body = "{customer_name} 様

いつもご利用いただき、誠にありがとうございます。

ご注文いただきました商品を発送いたしましたので、お知らせいたします。

【注文情報】
注文ID: {order_id}
配送業者: {carrier}
送り状番号: {tracking_number}";

    $previewData = [
        "customer_name" => "山田太郎",
        "order_id" => "ORD-2026-001",
        "tracking_number" => "123456789012",
        "carrier" => "ヤマト運輸",
    ];

    $showPreview = false;
    $previewSubject = $subject;
    $previewBody = $body;

    if ($request->has('preview')) {
        $showPreview = true;

        foreach ($previewData as $key => $value) {
            $previewSubject = str_replace("{{$key}}", $value, $previewSubject);
            $previewBody = str_replace("{{$key}}", $value, $previewBody);
        }
    }

    return view('btoc.email_settings', compact(
        'templateName',
        'subject',
        'body',
        'showPreview',
        'previewSubject',
        'previewBody'
    ));
}
}
