<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Opname {{ $opname->kode_opname }}</title>
    <style>
        @page { margin: 15mm; size: A4; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
        }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .sub { color: #555; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .center { text-align: center; }
        .ringkasan td { border: none; padding: 2px 10px 2px 0; }
        .selisih-lebih { color: #0369a1; }
        .selisih-kurang { color: #b91c1c; }
        .ttd { margin-top: 40px; display: flex; justify-content: space-between; }
        .ttd div { width: 45%; text-align: center; }
        .ttd .garis { margin-top: 50px; border-top: 1px solid #333; padding-top: 4px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()">Cetak</button>
    </div>

    <h1>Laporan Stok Opname — {{ $opname->kode_opname }}</h1>
    <div class="sub">
        Periode: {{ $opname->tanggal_mulai->format('d/m/Y') }}
        @if ($opname->tanggal_selesai) s/d {{ $opname->tanggal_selesai->format('d/m/Y') }} @endif
        &nbsp;|&nbsp; Status: {{ ucfirst($opname->status) }}
        &nbsp;|&nbsp; Dibuat oleh: {{ $opname->pembuat->name }}
        @if ($opname->penyelesai) &nbsp;|&nbsp; Diselesaikan oleh: {{ $opname->penyelesai->name }} @endif
    </div>

    @php
        $dihitung = $opname->detail->whereNotNull('stok_fisik');
        $sesuai = $dihitung->filter(fn ($d) => $d->selisih === 0)->count();
        $lebih = $dihitung->filter(fn ($d) => $d->selisih > 0)->count();
        $kurang = $dihitung->filter(fn ($d) => $d->selisih < 0)->count();
        $nilaiSelisih = $dihitung->sum(fn ($d) => (float) $d->nilai_selisih);
    @endphp

    <table class="ringkasan">
        <tr>
            <td><strong>Total Item:</strong> {{ $opname->detail->count() }}</td>
            <td><strong>Sesuai:</strong> {{ $sesuai }}</td>
            <td><strong>Lebih:</strong> {{ $lebih }}</td>
            <td><strong>Kurang:</strong> {{ $kurang }}</td>
            <td><strong>Nilai Selisih:</strong> Rp{{ number_format($nilaiSelisih, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Obat</th>
                <th>No. Batch</th>
                <th class="right">Stok Sistem</th>
                <th class="right">Stok Fisik</th>
                <th class="right">Selisih</th>
                <th class="right">Nilai Selisih</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($opname->detail as $detail)
                <tr>
                    <td>{{ $detail->batchObat->obat->nama_obat }}</td>
                    <td>{{ $detail->batchObat->no_batch }}</td>
                    <td class="right">{{ $detail->stok_sistem }}</td>
                    <td class="right">{{ $detail->stok_fisik ?? '-' }}</td>
                    <td class="right {{ $detail->selisih > 0 ? 'selisih-lebih' : ($detail->selisih < 0 ? 'selisih-kurang' : '') }}">
                        {{ $detail->selisih === null ? '-' : ($detail->selisih > 0 ? '+' . $detail->selisih : $detail->selisih) }}
                    </td>
                    <td class="right">{{ $detail->nilai_selisih === null ? '-' : 'Rp' . number_format((float) $detail->nilai_selisih, 0, ',', '.') }}</td>
                    <td>{{ $detail->catatan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd">
        <div>
            <div class="garis">Dihitung oleh<br>({{ $opname->pembuat->name }})</div>
        </div>
        <div>
            <div class="garis">Disetujui oleh<br>({{ $opname->penyelesai->name ?? '_________________' }})</div>
        </div>
    </div>
</body>
</html>
