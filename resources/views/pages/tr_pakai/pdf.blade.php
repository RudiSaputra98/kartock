<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Pakai</title>
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

        td.pakai-ball,
        td.pakai-pcs,
        td.total-pakai,
        td.reject,
        {
        font-size: 13px;
        text-align: center;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .header-row h3 {
            margin: 0;
        }
    </style>

</head>

<body>
    <div class="header">
        <h1>Laporan Transaksi Pakai</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</h2>
        <table style="width: 100%; margin-bottom: 10px; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; font-size: 14px;">
                    <strong>Kategori: {{ $categoryName }}</strong>
                </td>
                <td style="text-align: right; font-size: 14px;">
                    <strong>Mesin: {{ $mesinName }}</strong>

                </td>
            </tr>
        </table>


    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Pakai Ball</th>
                    <th>Isi Per Ball</th>
                    <th>Pakai Pcs</th>
                    <th>Reject</th>
                    <th>Jumlah Pakai</th>
                    <th>Kategori</th>
                    <th>Mesin</th>
                    <th>Note</th>

                </tr>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $trpakai)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($trpakai->tanggal)->format('d/m/Y') }}</td>
                        <td class="pakai-ball">
                            {{ $trpakai->pakai_ball_display > 0 ? number_format((float) $trpakai->pakai_ball_display, 0, ',', '.') : '-' }}
                        </td>
                        <td>
                            {{ $trpakai->isi_perball_name_display > 0 ? number_format((float) $trpakai->isi_perball_name_display, 0, ',', '.') : '-' }}
                        </td>
                        <td class="pakai-pcs">
                            {{ $trpakai->pakai_pcs_display > 0 ? number_format((float) $trpakai->pakai_pcs_display, 0, ',', '.') : '-' }}
                        </td>
                        <td class="reject">
                            {{ $trpakai->reject_display > 0 ? number_format((float) $trpakai->reject_display, 0, ',', '.') : '-' }}
                        </td>

                        <td class="total-pakai">
                            {{ $trpakai->jumlah_pakai > 0 ? number_format((float) $trpakai->jumlah_pakai, 0, ',', '.') : '-' }}
                        </td>
                        <td>{{ $trpakai->category->name }}</td>
                        <td>{{ $trpakai->mesin->name }}</td>
                        <td>{{ $trpakai->note }}</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: center; font-weight: bold;">
                        <strong>TOTAL </strong>
                    </td>
                    <td>
                        {{ $data->sum('pakai_ball') > 0 ? number_format((float) $data->sum('pakai_ball'), 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                    <td>
                        {{ $data->sum('pakai_pcs') > 0 ? number_format((float) $data->sum('pakai_pcs'), 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        {{ $data->sum('reject') > 0 ? number_format((float) $data->sum('reject'), 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        {{ $data->sum('jumlah_pakai') > 0 ? number_format((float) $data->sum('jumlah_pakai'), 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
        <p>Tanggal dan Waktu: {{ now()->format('d-m-Y H:i:s') }}</p>
        <a href="{{ route('tr_pakai.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</body>

</html>
