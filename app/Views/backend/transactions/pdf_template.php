<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Pembagian Saldo</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        .header h4 {
            margin: 5px 0;
            font-size: 13px;
            color: #555;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #777;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #222;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
            color: white;
            text-align: center;
        }
        .badge-success { background-color: #28a745; }
        .badge-secondary { background-color: #6c757d; }
        .badge-info { background-color: #17a2b8; }
        
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-muted { color: #777; }
        
        .adjustments-list {
            margin: 5px 0 0 0;
            padding-left: 15px;
            list-style-type: disc;
            font-size: 9px;
            color: #555;
        }
        .adjustments-list li {
            margin-bottom: 1px;
        }
        .divider {
            border-top: 2px dashed #ccc;
            margin: 25px 0;
            page-break-after: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Aplikasi Split Bill Keluarga</h2>
        <h4>Rekapitulasi Pembagian Saldo</h4>
        <p>Periode: <?= esc($calculationResult['period']['label'] ?? 'Semua') ?></p>
    </div>

    <div class="section-title">Rekapitulasi Pembagian Saldo</div>
    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 20%;">Nama</th>
                <th style="width: 10%;">Status</th>
                <th class="text-right" style="width: 14%;">Total Belanja (A)</th>
                <th class="text-right" style="width: 14%;">Shared (B)</th>
                <th class="text-right" style="width: 14%;">Selisih (A-B)</th>
                <th class="text-right" style="width: 14%;">Kustom (C)</th>
                <th class="text-right" style="width: 14%;">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($calculationResult['participants'] as $p): ?>
                <?php
                $selisihAwal = $p['total_paid'] - $p['shared_share'];
                $netBalance = $p['net_balance'];
                ?>
                <tr>
                    <td class="text-left font-weight-bold"><?= esc($p['username']) ?></td>
                    <td class="text-center">
                        <?php if ($p['is_active_member']): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">Rp <?= number_format($p['total_paid'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($p['shared_share'], 0, ',', '.') ?></td>
                    <td class="text-right font-weight-bold <?= $selisihAwal >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $selisihAwal >= 0 ? '+' : '-' ?> Rp <?= number_format(abs($selisihAwal), 0, ',', '.') ?>
                    </td>
                    <td class="text-right">Rp <?= number_format($p['individual_charge'], 0, ',', '.') ?></td>
                    <td class="text-right font-weight-bold <?= $netBalance >= 0 ? 'text-success' : 'text-danger' ?>" style="background-color: <?= $netBalance >= 0 ? '#f0fcf0' : '#fcf0f0' ?>;">
                        <?= $netBalance >= 0 ? '+' : '-' ?> Rp <?= number_format(abs($netBalance), 0, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <?php if (!empty($calculationResult['settlements'])): ?>
        <div class="section-title" style="background-color: #fff9e6; padding: 6px; border-left: 3px solid #ffc107; font-size: 11px;">Rekomendasi Transfer (Settlement)</div>
        <table style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th class="text-left" style="background-color: #ffeeba; color: #856404; border: 1px solid #f5c6cb; width: 40%;">Siapa yang Bayar</th>
                    <th class="text-center" style="background-color: #ffeeba; color: #856404; border: 1px solid #f5c6cb; width: 20%;">Nominal</th>
                    <th class="text-right" style="background-color: #ffeeba; color: #856404; border: 1px solid #f5c6cb; width: 40%;">Bayar Kepada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($calculationResult['settlements'] as $s): ?>
                    <tr>
                        <td class="text-left font-weight-bold" style="color: #dc3545; border: 1px solid #ddd;"><?= esc($s['from_username']) ?></td>
                        <td class="text-center font-weight-bold" style="color: #007bff; border: 1px solid #ddd;">Rp <?= number_format($s['amount'], 0, ',', '.') ?></td>
                        <td class="text-right font-weight-bold" style="color: #28a745; border: 1px solid #ddd;"><?= esc($s['to_username']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="background-color: #d4edda; color: #155724; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; margin-bottom: 20px; text-align: center;">
            Semua saldo seimbang! Tidak ada transaksi transfer yang perlu dilakukan.
        </div>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="header">
        <h4>Rincian Transaksi Detail</h4>
        <p>Daftar lengkap transaksi pengeluaran</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 15%; text-align: center;">Tanggal</th>
                <th style="width: 45%; text-align: left;">Deskripsi / Detail Beban</th>
                <th style="width: 12%; text-align: center;">Tipe</th>
                <th style="width: 13%; text-align: center;">Pembayar</th>
                <th style="width: 15%; text-align: right;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 20px;">Belum ada transaksi.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td class="text-center" style="white-space: nowrap;"><?= date('d M Y', strtotime($t['date'])) ?></td>
                        <td class="text-left">
                            <span class="font-weight-bold" style="font-size: 11px;"><?= esc($t['description']) ?></span>
                            <div class="text-muted" style="font-size: 8px; margin-top: 1px;">
                                Dicatat oleh: <?= esc($t['creator_name']) ?> pada <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                            </div>
                            <?php if ($t['type'] === 'individual' && !empty($t['adjustments'])): ?>
                                <ul class="adjustments-list">
                                    <?php foreach ($t['adjustments'] as $adj): ?>
                                        <li>
                                            <?= esc($adj['username']) ?>: <strong>Rp <?= number_format($adj['amount'], 0, ',', '.') ?></strong>
                                            <?= $adj['note'] ? '<span class="text-muted">(' . esc($adj['note']) . ')</span>' : '' ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($t['type'] === 'shared'): ?>
                                <span class="badge badge-success">Shared</span>
                            <?php else: ?>
                                <span class="badge badge-info">Individual</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= esc($t['paid_by_name']) ?></td>
                        <td class="text-right font-weight-bold">Rp <?= number_format($t['amount'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
