<!DOCTYPE html>
<html>
<head>
    <title>Pemberitahuan Pembayaran Gaji</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>Yth. Bapak/Ibu {{ $gaji->karyawan->nama }},</p>

    <p>Bersama email ini, kami informasikan bahwa gaji Anda untuk periode saat ini telah berhasil diproses dan dibayarkan ke rekening Anda.</p>

    <p>Berikut adalah rincian pembayaran gaji Anda:</p>
    <ul>
        <li><strong>Nomor Slip:</strong> {{ $gaji->no_slip }}</li>
        <li><strong>Tanggal Pembayaran:</strong> {{ \Carbon\Carbon::parse($gaji->tgl)->format('d F Y') }}</li>
        <li><strong>Gaji Pokok:</strong> Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</li>
        <li><strong>Tunjangan:</strong> Rp {{ number_format($gaji->tunjangan, 0, ',', '.') }}</li>
        <li><strong>Potongan:</strong> Rp {{ number_format($gaji->potongan, 0, ',', '.') }}</li>
        <li><strong>Total Gaji Diterima:</strong> Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</li>
    </ul>

    <p>Harap periksa kembali mutasi rekening Anda. Jika ada ketidaksesuaian atau pertanyaan lebih lanjut mengenai rincian di atas, silakan hubungi tim HRD atau Keuangan.</p>

    <p>Terima kasih atas kerja keras dan dedikasi Anda selama ini bersama kami.</p>

    <br>
    <p>Salam hangat,</p>
    <p><strong>Manajemen Mora Market</strong></p>
</body>
</html>
