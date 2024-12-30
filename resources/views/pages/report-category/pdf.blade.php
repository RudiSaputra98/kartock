{{-- @php
use App\Models\Category;
@endphp

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            padding: 10px 0;
            background-color: #f2f2f2;
        }
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <h1>Laporan Transaksi2</h1>
    <div class="filters">
        <p><strong>Periode:</strong> {{ $startDate }} - {{ $endDate }}</p>
        @if ($categoryFilter && $categoryFilter != null)
        <p><strong>Kategori:</strong> {{ Category::find($categoryFilter)->name ?? 'Tidak Diketahui' }}</p>
        @else
        <p><strong>Kategori:</strong> Semua Kategori</p>
        @endif
    </div>

    @if ($categoryFilter == null)
        @foreach ($reportData->groupBy('category') as $category => $transactions)
            <h2>{{ $category }}</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Masuk</th>
                        <th>Pakai</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: bold;">Stok Awal</td>
                        <td style="font-weight: bold;">
                            {{ number_format($initialStocksArray->get($category, 0), 0, ',', '.') }}
                        </td>
                    </tr>
                    @foreach ($transactions as $data)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d-m-Y') }}</td>
                            <td>{{ $data['category'] }}</td>
                            <td>{{ number_format($data['tr_masuk'], 0, ',', '.') }}</td>
                            <td>{{ number_format($data['tr_pakai'], 0, ',', '.') }}</td>
                            <td>{{ number_format($data['stok'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Masuk</th>
                    <th>Pakai</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Stok Awal</td>
                    <td style="font-weight: bold;">
                        {{ number_format($initialStocksArray->get(Category::find($categoryFilter)->name ?? 'Tidak Diketahui', 0), 0, ',', '.') }}
                    </td>
                </tr>
                @foreach ($reportData as $data)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d-m-Y') }}</td>
                        <td>{{ $data['category'] }}</td>
                        <td>{{ number_format($data['tr_masuk'], 0, ',', '.') }}</td>
                        <td>{{ number_format($data['tr_pakai'], 0, ',', '.') }}</td>
                        <td>{{ number_format($data['stok'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Generated on {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html> --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Kategori </title>
    <style>
        body {
            font-family: "Arial", sans-serif;
            margin: 20px;
            font-size: 12px;
            color: #000000;
            background-color: #f9f9f9;
        }

        h1,
        h2,
        {
        text-align: center;
        margin: 0 0 20px 0;
        }


        h3 {

            margin: 0 0 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        th {
            background-color: #0e2d7b;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        tfoot td {
            font-weight: bold;
            text-align: center;
            background-color: #c9dc18;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #666666;
        }

        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        tbody tr:hover {
            background-color: #d1ecf1;
        }

        td.tr-pakai,
        td.tr-masuk,
        td.stok {
            font-size: 13px;
            text-align: center;
        }
    </style>

</head>

<body>
    <div>
        <h1>Laporan Transaksi Per Kategori</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</h2>
    </div>

    @if (!empty($reportData))
        @foreach ($reportData as $categoryId => $data)
            @if ($data->isNotEmpty())
                <div class="header">
                    <h3><strong>Kategori: {{ $data->first()['category'] ?? 'Tidak Diketahui' }}</strong></h3>
                    <h3>Stok Awal:
                        @php
                            $initialStock = $initialStocks[$categoryId] ?? null;
                            $stokAwal = $initialStock ? $initialStock['total_masuk'] - $initialStock['total_pakai'] : 0;
                        @endphp
                        {{ number_format($stokAwal, 0, ',', '.') }} Pcs
                    </h3>

                </div>

                <table style="margin-bottom: 30px;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 15%;">Masuk (Pcs)</th>
                            <th style="width: 15%;">Keluar (Psc)</th>
                            <th style="width: 15%;">Stok (Pcs)</th>
                            <th style="width: 35%;">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $row)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                                {{-- <td>{{ $row['tanggal'] ?? '-' }}</td> --}}
                                <td class="tr-masuk">
                                    {{ $row['tr_masuk'] ? number_format($row['tr_masuk'], 0, ',', '.') : '-' }}</td>

                                <td class="tr-pakai">
                                    {{ $row['tr_pakai'] ? number_format($row['tr_pakai'], 0, ',', '.') : '-' }}</td>
                                <td class="stok">
                                    {{ $row['stok'] ? number_format($row['stok'], 0, ',', '.') : '-' }}</td>


                                <td class="note">
                                    @php
                                        // Ambil stok yang ada pada row
                                        $stok = $row['stok'] ?? 0;
                                        $category = $row['category'] ?? '';

                                        // Cek kategori dan tentukan pembagi
                                        if (str_contains(strtolower($category), 'karung')) {
                                            // Jika kategori mengandung 'karung', bagi dengan 500
                                            $note = $stok / 500;
                                            $remaining = $stok % 500; // Sisa stok yang tidak habis dibagi 500
                                            $noteText =
                                                floor($note) .
                                                ' ball' .
                                                ($remaining > 0
                                                    ? ' + ' . number_format($remaining, 0, ',', '.') . ' pcs'
                                                    : '');
                                        } elseif (str_contains(strtolower($category), 'bag')) {
                                            // Jika kategori mengandung 'bag', bagi dengan 20
                                            $note = $stok / 20;
                                            $remaining = $stok % 20; // Sisa stok yang tidak habis dibagi 20
                                            $noteText =
                                                floor($note) .
                                                ' ball' .
                                                ($remaining > 0
                                                    ? ' + ' . number_format($remaining, 0, ',', '.') . ' pcs'
                                                    : '');
                                        } else {
                                            // Jika tidak ada kategori yang cocok, gunakan stok langsung
                                            $noteText = $stok . ' pcs';
                                        }
                                    @endphp
                                    {{ $noteText }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="text-align: center; font-weight: bold;">
                                <strong>TOTAL </strong>
                            </td>
                            <td>
                                {{ collect($data)->sum('tr_masuk') > 0 ? number_format((float) collect($data)->sum('tr_masuk'), 0, ',', '.') : '-' }}
                            </td>
                            <td>
                                {{ collect($data)->sum('tr_pakai') > 0 ? number_format((float) collect($data)->sum('tr_pakai'), 0, ',', '.') : '-' }}
                            </td>
                            <td class="stok">
                                {{ $row['stok'] ? number_format($row['stok'], 0, ',', '.') : '-' }}</td>
                            <td>{{ $data->first()['category'] ?? 'Tidak Diketahui' }}</td>
                        </tr>
                    </tfoot>


                </table>
            @endif
        @endforeach
    @else
        <p>Tidak ada data untuk ditampilkan.</p>
    @endif



    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
        <p>Tanggal dan Waktu: {{ now()->format('d-m-Y H:i:s') }}</p>
        <a href="{{ route('report.category.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</body>

</html>
