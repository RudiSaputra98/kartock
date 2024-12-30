<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Masuk</title>
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

        td.masuk-ball,
        td.masuk-pcs,
        td.total-masuk {
            font-size: 13px;
            text-align: center;
        }
    </style>

</head>

<body>
    <div class="header">
        <h1>Laporan Transaksi Masuk</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</h2>
        <h3><strong>Kategori: {{ $categoryName }}</strong></h3>
    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 8%;">Tanggal</th>
                    <th style="width: 5%;">Masuk (Ball)</th>
                    <th style="width: 5%;">Isi Per Ball</th>
                    <th style="width: 5%;">Masuk (Pcs)</th>
                    <th style="width: 5%;">Total Masuk (Pcs)</th>
                    <th style="width: 22%;">Kategori</th>
                    <th style="width: 45%;">Note</th>
                </tr>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $trmasuk)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($trmasuk->tanggal)->format('d/m/Y') }}</td>
                        <td class="masuk-ball">
                            {{ $trmasuk->masuk_ball_display > 0 ? number_format((float) $trmasuk->masuk_ball_display, 0, ',', '.') : '-' }}
                        </td>
                        <td>
                            {{ $trmasuk->isi_perball_name_display > 0 ? number_format((float) $trmasuk->isi_perball_name_display, 0, ',', '.') : '-' }}
                        </td>
                        <td class="masuk-pcs">
                            {{ $trmasuk->masuk_pcs_display > 0 ? number_format((float) $trmasuk->masuk_pcs_display, 0, ',', '.') : '-' }}
                        </td>
                        <td class="total-masuk">
                            {{ $trmasuk->jumlah_masuk > 0 ? number_format((float) $trmasuk->jumlah_masuk, 0, ',', '.') : '-' }}
                        </td>
                        <td>{{ $trmasuk->category->name }}</td>
                        <td>{{ $trmasuk->note }}</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: center; font-weight: bold;">
                        <strong>TOTAL </strong>
                    </td>
                    <td>
                        {{ $data->sum('masuk_ball') > 0 ? number_format((float) $data->sum('masuk_ball'), 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                    <td>
                        {{ $data->sum('masuk_pcs') > 0 ? number_format((float) $data->sum('masuk_pcs'), 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        {{ $data->sum('jumlah_masuk') > 0 ? number_format((float) $data->sum('jumlah_masuk'), 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
        <p>Tanggal dan Waktu: {{ now()->format('d-m-Y H:i:s') }}</p>
        <a href="{{ route('tr_masuk.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</body>

</html>