<?php

namespace App\Exports;

use App\Models\Mesin;
use App\Models\TrPakai;
use App\Models\Category;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;


class ExcellPakai implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $startDate;
    protected $endDate;
    protected $category;
    protected $mesin;

    public function __construct($startDate, $endDate, $category, $mesin)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->category = $category;
        $this->mesin = $mesin;
    }

    // **Data Collection**
    public function collection()
    {
        return TrPakai::with(['category', 'isiPerball'])
            ->when($this->startDate, fn($query) => $query->whereDate('tanggal', '>=', $this->startDate))
            ->when($this->endDate, fn($query) => $query->whereDate('tanggal', '<=', $this->endDate))
            ->when($this->category, fn($query) => $query->where('category_id', $this->category)) // Hanya filter jika kategori ada
            ->when($this->mesin, fn($query) => $query->where('mesin_id', $this->mesin)) // Hanya filter jika kategori ada
            ->get();
    }

    // **Kolom Header**
    public function headings(): array
    {
        return [
            'Tanggal',
            'Pakai Ball',
            'Isi Per Ball',
            'Pakai Pcs',
            'Reject',
            'Jumlah Pakai',
            'Kategori',
            'Mesin',
            'Note',
        ];
    }

    // **Mapping Data ke Kolom**
    public function map($row): array
    {
        return [
            $row->tanggal,
            $row->pakai_ball,
            $row->isiPerball->name ?? '-', // Isi Per Ball, default '-' jika null
            $row->pakai_pcs,
            $row->reject,
            $row->jumlah_pakai,
            $row->category->name ?? '-',  // Nama kategori, default '-' jika null
            $row->mesin->name ?? '-',
            $row->note,
        ];
    }

    // **Start Data dari Cell A5**
    public function startCell(): string
    {
        return 'A6';
    }

    // **Custom Header untuk A1 hingga A4**
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Header di A1 hingga A4
                $sheet->setCellValue('A1', 'Laporan Transaksi Pakai');
                $sheet->setCellValue('A2', 'Periode: ' . $this->startDate . ' - ' . $this->endDate);
                $categoryName = $this->category ? Category::find($this->category)->name : 'Semua Kategori';
                $sheet->setCellValue('A3', 'Kategori: ' . $categoryName);

                $mesinName = $this->mesin ? Mesin::find($this->mesin)->name : 'Semua Mesin';
                $sheet->setCellValue('A4', 'Mesin: ' . $mesinName);

                // Format tebal untuk A1
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(10); // Kolom A
                $sheet->getColumnDimension('B')->setWidth(8); // Kolom B
                $sheet->getColumnDimension('C')->setWidth(8); // Kolom C
                $sheet->getColumnDimension('D')->setWidth(8); // Kolom D
                $sheet->getColumnDimension('E')->setWidth(8); // Kolom E
                $sheet->getColumnDimension('F')->setWidth(10); // Kolom F
                $sheet->getColumnDimension('G')->setWidth(25); // Kolom G
                $sheet->getColumnDimension('H')->setWidth(7); // Kolom H
                $sheet->getColumnDimension('I')->setWidth(30); // Kolom I

                $sheet->getStyle('A6:I6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);


                $sheet->getStyle('A6:I' . $sheet->getHighestRow())->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
