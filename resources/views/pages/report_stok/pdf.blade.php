<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Barang </title>
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
        h3 {
            text-align: center;
            margin: 0 0 20px 0;
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
    <div class="header">
        <h1>Laporan Stok Barang</h1>
        <h2>Per Tanggal : {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</h2>
    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Rincian</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groupedData as $key => $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        {{-- <td>{{ $data['tanggal'] }}</td> --}}
                        <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d/m/Y') }}</td>
                        <td>{{ $data['category'] }}</td>
                        <td class="stok">
                            {{ $data['stok'] ? number_format($data['stok'], 0, ',', '.') : '-' }}
                        </td>

                        <td>{{ $data['rincian'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data tersedia</td>
                    </tr>
                @endforelse
            </tbody>

        </table>



    </div>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
        <p>Tanggal dan Waktu: {{ now()->format('d-m-Y H:i:s') }}</p>
        <a href="{{ route('report.stok.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</body>

</html>
