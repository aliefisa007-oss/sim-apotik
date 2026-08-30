<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $transaksi->no_transaksi }}</title>
    <style>
        @page { margin: 0; size: 58mm auto; }
        body {
            width: 58mm;
            margin: 0;
            padding: 4mm;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .item-name { word-break: break-word; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="center bold">APOTIK</div>
    <div class="center">{{ $transaksi->created_at->format('d/m/Y H:i') }}</div>
    <div class="center">No: {{ $transaksi->no_transaksi }}</div>
    <hr>

    <table>
        @foreach ($transaksi->detail as $item)
            <tr>
                <td colspan="2" class="item-name">{{ $item->obat->nama_obat }}</td>
            </tr>
            <tr>
                <td>{{ $item->jumlah }} x {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <hr>
    <table>
        <tr>
            <td class="bold">TOTAL</td>
            <td class="right bold">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>{{ ucfirst($transaksi->metode_bayar) }}</td>
            <td class="right">{{ $transaksi->jumlah_bayar ? 'Rp' . number_format($transaksi->jumlah_bayar, 0, ',', '.') : '-' }}</td>
        </tr>
        @if ($transaksi->kembalian !== null)
            <tr>
                <td>Kembali</td>
                <td class="right">Rp{{ number_format($transaksi->kembalian, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <hr>
    <div class="center">Kasir: {{ $transaksi->kasir->name }}</div>
    @if ($transaksi->apotekerApproval)
        <div class="center">Apoteker: {{ $transaksi->apotekerApproval->name }}</div>
    @endif
    <div class="center" style="margin-top: 6px;">Terima kasih</div>

    <div class="no-print center" style="margin-top: 12px;">
        <button onclick="window.print()">Cetak</button>
        <a href="{{ route('penjualan.kasir') }}" style="margin-left: 8px;">Transaksi Baru</a>
    </div>

    <script>
        // Auto-print bisa diaktifkan sesuai kebutuhan operasional:
        // window.onload = () => window.print();
    </script>
</body>
</html>
