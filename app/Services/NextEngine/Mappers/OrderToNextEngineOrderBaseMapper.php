<?php

namespace App\Services\NextEngine\Mappers;

use App\Models\Order;
use App\Models\OrderProduct;

class OrderToNextEngineOrderBaseMapper
{
    public function map(Order $order, OrderProduct $item): array
    {
        $user = $order->user;
        $transaction = $order->transaction;

        $order_address_json = $order->order_address;
        if (is_string($order_address_json)) {
            $order_address_json = json_decode($order_address_json, true) ?: [];
        }

        return [
            '店舗伝票番号' => $order->id,
            '受注日' => $order->created_at->format('Y/m/d H:i:s'),

            // 受注番号 đang map zip → sai → phải là order code / tracking nội bộ.
            '受注郵便番号' => $order_address_json['zip'] ?? '',
            
            '受注住所１' => $order_address_json['address'] ?? '',
            '受注住所２' => $order_address_json['address'] ?? '',
            '受注名' => $order_address_json['name'] ?? '',
            '受注名カナ' => $order_address_json['name'] ?? '',
            '受注電話番号' => $order_address_json['phone'] ?? '',
            '受注メールアドレス' => $order_address_json['email'] ?? '',
            '発送郵便番号' => $order_address_json['zip'] ?? '',
            '発送先住所１' => $order_address_json['address'] ?? '',
            '発送先住所２' => $order_address_json['address'] ?? '',
            '発送先名' => $order_address_json['name'] ?? '',

            // 受注者カナ không nên dùng name → nếu không có kana → để ""
            '発送先カナ' => $order_address_json['name'] ?? '',
            '発送電話番号' => $order_address_json['phone'] ?? '',

            // Production: phải mapping theo DB
            '支払方法' => 'クレジットカード',
            '発送方法' => '宅急便',

            '商品計' => $order->amount,
            '税金' => 0,
            '発送料' => 0,
            '手数料' => 0,
            'ポイント' => 0,
            'その他費用' => 0,

            // → Sai. Phải = amount
            '合計金額' => 0,

            'ギフトフラグ' => 0,
            '時間帯指定' => '',
            '日付指定' => '',
            '作業者欄' => '',
            '備考' => '',
            '商品名' => $item->product_name,
            '商品コード' => $item->product_id,
            '商品価格' => $item->unit_price,
            '受注数量' => $item->qty,
            '商品オプション' => '',
            '出荷済フラグ' => 0,
            '顧客区分' => '',
            '顧客コード' => '',

// 消費税率 (%) đang "" → Nếu NextEngine yêu cầu numeric → Có thể gây reject
            '消費税率（%）' => '',

            'のし' => '',
            'ラッピング' => '',
            'メッセージ' => '',
        ];
    }
}
