@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>DASHBOARD</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">
                    <!-- Menampilkan foto user -->
                    <img src="{{ asset('storage/images/' . $user->photo) }}" alt="User Photo"
                        style="height: 30px; margin-right: 10px; mix-blend-mode: multiply">
                    Welcome, {{ $user->name }}!
                </li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <style>
        .progress {
            position: relative;
        }

        .progress-text {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            color: black;
        }

        @media (max-width: 767px) {
            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 6px;
            }

            .progress {
                height: 5px;
            }

            .progress-wrapper {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 0.75rem;
                margin-top: 0;
            }

            .progress-wrapper small {
                color: #6c757d;
            }

            .progress-text {
                font-size: 0.75rem;
            }

            .text-end {
                margin-top: 0;
                padding-left: 5px;
                font-size: 0.75rem;
                color: #6c757d;
            }
        }
    </style>
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('tr_masuk.create') }}" class="info-box-link d-block text-decoration-none shadow-lg">
                <div class="info-box border border-info rounded-lg">
                    <span class="info-box-icon bg-info text-white rounded-left"><i class="fas fa-laptop-medical"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold text-info">Input <br>Transaksi Masuk</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('tr_pakai.create') }}" class="info-box-link d-block text-decoration-none shadow-lg">
                <div class="info-box border border-info rounded-lg">
                    <span class="info-box-icon bg-info text-white rounded-left"><i class="fas fa-folder-minus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold text-info">Input <br>Transaksi Pakai</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('report.index') }}" class="info-box-link d-block text-decoration-none shadow-lg">
                <div class="info-box border border-info rounded-lg">
                    <span class="info-box-icon bg-info text-white rounded-left"><i class="fas fa-receipt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold text-info">Laporan <br>In Out</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('report.category.index') }}" class="info-box-link d-block text-decoration-none shadow-lg">
                <div class="info-box border border-info rounded-lg">
                    <span class="info-box-icon bg-info text-white rounded-left"><i class="fas fa-receipt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold text-info">Laporan <br>Kategori</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr style="text-align: center;">
                            <th style="width: 8%;">Tanggal</th>
                            <th style="width: 27%;">Kategori</th>
                            <th style="width: 10%;">Stok (Pcs)</th>
                            <th style="width: 40%;">Indikator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groupedData as $key => $data)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d/m/Y') }}</td>
                                <td>{{ $data['category'] }}</td>
                                <td>{{ $data['stok'] ? number_format($data['stok'], 0, ',', '.') : '-' }}</td>
                                <td>
                                    <div>
                                        <div class="progress flex-grow-1 position-relative" style="height: 15px;">
                                            @if ($data['stockPercentage'] == 100)
                                                <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="100"
                                                    aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                                            @elseif ($data['stockPercentage'] >= 60 && $data['stockPercentage'] < 100)
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    aria-valuenow="{{ $data['stockPercentage'] }}" aria-valuemin="0"
                                                    aria-valuemax="100" style="width: {{ $data['stockPercentage'] }}%">
                                                </div>
                                            @elseif ($data['stockPercentage'] >= 20 && $data['stockPercentage'] < 60)
                                                <div class="progress-bar bg-warning" role="progressbar"
                                                    aria-valuenow="{{ $data['stockPercentage'] }}" aria-valuemin="0"
                                                    aria-valuemax="100" style="width: {{ $data['stockPercentage'] }}%">
                                                </div>
                                            @else
                                                <div class="progress-bar bg-secondary" role="progressbar" aria-valuenow="0"
                                                    aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                                            @endif
                                        </div>
                                        <div class="progress-wrapper d-flex justify-content-between mt-2">
                                            <small class="text-muted"> Perkiraan Habis :
                                                @if (is_numeric($data['daysLeft']))
                                                    {{ $data['daysLeft'] }} hari
                                                @else
                                                    <span class="text-warning">{{ $data['daysLeft'] }}</span>
                                                @endif
                                            </small>
                                            <small class="text-muted">
                                                Max:
                                                <small>{{ $data['max_stok'] ? number_format($data['max_stok'] * 1.4, 0, ',', '.') : '-' }}</small>
                                            </small>
                                        </div>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card direct-chat direct-chat-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Notes</h3>
                </div>

                <div class="card-body">
                    <div style="max-height: 340px; overflow-y: auto; padding: 30px;">
                        @forelse ($notes as $note)
                            <div class="direct-chat-msg @if (auth()->user()->id == $note->user->id) right @else left @endif">
                                <div class="direct-chat-infos clearfix">
                                    <span
                                        class="direct-chat-name @if (auth()->user()->id == $note->user->id) float-right @else float-left @endif">
                                        {{ $note->user->name }}
                                    </span>
                                    <span
                                        class="direct-chat-timestamp @if (auth()->user()->id == $note->user->id) float-left @else float-right @endif">
                                        {{ $note->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>
                                <img class="direct-chat-img" src="{{ asset('storage/images/' . $note->user->photo) }}"
                                    alt="message user image">
                                <div class="direct-chat-text">
                                    {{ $note->content }}
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">Belum ada catatan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card-footer">
                    <div class="input-group">
                        <input id="note-input" type="text" name="message" placeholder="Tulis catatan..."
                            class="form-control">
                        <span class="input-group-append">
                            <button type="button" id="send-note" class="btn btn-primary">+</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card direct-chat direct-chat-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">LOG Activity</h3>
                </div>

                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div style="overflow-x: auto;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Waktu</th>
                                    <th>Aktivitas</th>
                                    <th>Deskripsi</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->user->name }}</td>
                                        <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                                        <td>{{ $log->activity_type }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>
                                            @if ($log->data)
                                                <button class="btn btn-info view-details"
                                                    data-id="log-{{ $log->id }}" data-toggle="modal"
                                                    data-target="#dataModal-{{ $log->id }}"
                                                    style="background: transparent; border: none;">
                                                    <i class="fas fa-eye" style="color: rgb(47, 47, 203);"></i>
                                                </button>

                                                <div class="modal fade" id="dataModal-{{ $log->id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="dataModalLabel-{{ $log->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="dataModalLabel-{{ $log->id }}">Detail Data
                                                                </h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <pre>{{ $log->formattedData }}</pre>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                Tidak ada data
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Handling note submission
        document.getElementById('send-note').addEventListener('click', function() {
            var note = document.getElementById('note-input').value;

            if (note.trim() !== "") {
                fetch('{{ route('notes.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            note: note
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('note-input').value = '';
                            showTemporaryMessage('Catatan berhasil disimpan!');
                            location.reload();
                        } else {
                            alert('Terjadi kesalahan saat menyimpan catatan.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            } else {
                alert('Tolong isi catatan terlebih dahulu.');
            }
        });

        // Temporary message function
        function showTemporaryMessage(message) {
            var alertBox = document.createElement('div');
            alertBox.classList.add('alert', 'alert-success');
            alertBox.textContent = message;
            document.body.appendChild(alertBox);

            setTimeout(function() {
                alertBox.remove();
            }, 10000);
        }
    </script>
@endsection
