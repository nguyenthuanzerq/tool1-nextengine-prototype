<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WorkInstructionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Lấy dữ liệu truyền vào
     */
    public function collection()
    {
        return $this->orders;
    }

    /**
     * Định nghĩa Tiêu đề các cột (Dòng 1)
     */
    public function headings(): array
    {
        return [
            '受注番号 (Mã ĐH)',
            '店舗 (Shop)',
            '受注日 (Ngày đặt)',
            '購入者名 (Người mua)',
            '商品名 (Sản phẩm)',
            '合計金額 (Tổng tiền)'
        ];
    }

    /**
     * Map dữ liệu của từng dòng order vào đúng cột tương ứng
     */
    public function map($order): array
    {
        $productsName = $order->products->pluck('product_name')->implode(" \n ");

        return [
            $order->receipt_receipt_id,
            $order->shop->shop_name ?? 'N/A',
            $order->receive_order_date ? $order->receive_order_date->format('Y-m-d H:i') : '',
            $order->purchaser_name,
            $productsName ?: 'N/A',
            '¥' . number_format($order->receive_order_total_amount) // Format số tiền cho đẹp
        ];
    }

    /**
     * Thiết lập độ rộng cột cho dễ nhìn
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25, // Mã ĐH
            'B' => 25, // Shop
            'C' => 20, // Ngày đặt
            'D' => 30, // Người mua
            'E' => 50, // Sản phẩm
            'F' => 20, // Tổng tiền
        ];
    }

    /**
     * Tùy chỉnh Style: Màu nền, Đóng khung, Căn chữ
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $lastColumn . $lastRow;

        // 1. Style cho Tiêu đề (Dòng 1)
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Chữ trắng
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'], // Nền xanh xám đậm (Tailwind slate-800)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Kẻ khung (Borders) cho toàn bộ bảng
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'], // Viền xám nhạt
                ],
            ],
        ]);

        // 3. Căn giữa, cho phép xuống dòng (Wrap Text) để hiển thị danh sách sản phẩm đẹp hơn
        $sheet->getStyle($range)->getAlignment()->setWrapText(true);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        // Căn phải riêng cho cột Tổng tiền
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}