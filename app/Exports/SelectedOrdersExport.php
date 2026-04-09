<?php

namespace App\Exports;

use App\Models\PlatformOrder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SelectedOrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithEvents
{
    use Exportable;
    use RegistersEventListeners;

    public function __construct(
        protected array $orderIds
    ) {
    }

    public function collection()
    {
        return PlatformOrder::query()
            ->with(['shop', 'items'])
            ->whereIn('id', $this->orderIds)
            ->orderByDesc('ordered_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            '数値順',
            'ショップ名',
            'ショップコード',
            'プラットフォーム注文ID',
            '商品名',
            '配送方法',
            '追跡番号',
            '購入者ID',
            '購入者名',
            '配送先住所',
            '電話',
            'メール',
            '注文日時',
        ];
    }

    public function map($order): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $order->shop->shop_name ?? '',
            $order->shop->shop_code ?? '',
            $order->platform_order_id,
            $order->items->first()?->product_name ?? '',
            $order->delivery_method ?? '',
            $order->tracking_number ?? '',
            $order->buyer_id ?? '',
            $order->buyer_name ?? '',
            $order->buyer_address ?? ($order->delivery_address ?? ''),
            $order->buyer_phone ?? '',
            $order->buyer_email ?? '',
            optional($order->ordered_at)->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '1D4ED8'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public static function afterSheet(AfterSheet $event): void
    {
        $sheet = $event->sheet->getDelegate();
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $range = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_DASHED,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
    }
}
