<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_auth();

$pageTitle = 'Gösterge Paneli';
$activePage = 'dashboard';

$totalProducts = (int)(fetch_one('SELECT COUNT(*) AS total FROM products')['total'] ?? 0);
$totalStock = (int)(fetch_one('SELECT COALESCE(SUM(quantity),0) AS total FROM products')['total'] ?? 0);
$lowStock = (int)(fetch_one('SELECT COUNT(*) AS total FROM products WHERE quantity <= reorder_point')['total'] ?? 0);

$entryData = fetch_all('SELECT DATE(created_at) as day, SUM(quantity) total FROM stock_entries WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at)');
$exitData = fetch_all('SELECT DATE(created_at) as day, SUM(quantity) total FROM stock_exits WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at)');

$entriesMap = [];
foreach ($entryData as $row) {
    $entriesMap[$row['day']] = (int)$row['total'];
}
$exitsMap = [];
foreach ($exitData as $row) {
    $exitsMap[$row['day']] = (int)$row['total'];
}

$chartLabels = [];
$entrySeries = [];
$exitSeries = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = $date;
    $entrySeries[] = $entriesMap[$date] ?? 0;
    $exitSeries[] = $exitsMap[$date] ?? 0;
}

$recentMovements = fetch_all(
    'SELECT "Entry" AS type, se.quantity, se.created_at, p.name AS product, u.username
     FROM stock_entries se
     INNER JOIN products p ON p.id = se.product_id
     INNER JOIN users u ON u.id = se.user_id
     UNION ALL
     SELECT "Exit" AS type, sx.quantity, sx.created_at, p.name AS product, u.username
     FROM stock_exits sx
     INNER JOIN products p ON p.id = sx.product_id
     INNER JOIN users u ON u.id = sx.user_id
     ORDER BY created_at DESC
     LIMIT 6'
);

include __DIR__ . '/includes/header.php';
?>
<div class="card hero-card mb-4">
    <div class="row g-0 align-items-center">
        <div class="col-lg-6 p-4 p-lg-5">
            <p class="text-uppercase small mb-2 text-white-50">Depo Özeti</p>
            <h3 class="fw-bold mb-3">Gerçek zamanlı stok takibi</h3>
            <p class="mb-4">Anlık giriş-çıkış grafikleri, kritik stok uyarıları ve ürün performansını tek ekrandan takip edin.</p>
            <div class="d-flex flex-wrap gap-3">
                <span class="badge bg-white text-primary">7 Günlük Trend</span>
                <span class="badge bg-white text-primary">Düşük Stok Alarmı</span>
            </div>
        </div>
        <div class="col-lg-6 hero-visual"></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card card-kpi p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon bg-primary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <p class="text-muted mb-1">Toplam Ürün</p>
                    <h2 class="fw-bold mb-0"><?= $totalProducts; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-kpi p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon bg-success"><i class="bi bi-graph-up"></i></div>
                <div>
                    <p class="text-muted mb-1">Toplam Stok</p>
                    <h2 class="fw-bold mb-0"><?= $totalStock; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-kpi p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon bg-danger"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <p class="text-muted mb-1">Düşük Stok Uyarısı</p>
                    <h2 class="fw-bold mb-0 text-danger"><?= $lowStock; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0">Stok Girişi vs Çıkışı</h6>
                        <small class="text-muted">Son 7 gün | miktar bazlı</small>
                    </div>
                </div>
                <canvas id="stockChart" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <h6 class="mb-3">Son Stok Hareketleri</h6>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentMovements as $movement): ?>
                        <?php
                        $typeLabel = $movement['type'] === 'Entry' ? 'Giriş' : 'Çıkış';
                        $typeClass = $movement['type'] === 'Entry' ? 'bg-success' : 'bg-danger';
                        ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge <?= $typeClass; ?>"><?= $typeLabel; ?></span>
                                    <strong class="ms-2"><?= htmlspecialchars($movement['product']); ?></strong>
                                    <div class="text-muted small">
                                        <?= (int)$movement['quantity']; ?> Adet · <?= htmlspecialchars($movement['username']); ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= date('d.m H:i', strtotime($movement['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($recentMovements)): ?>
                        <p class="text-muted small">Henüz stok hareketi yok.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.stockChartData = {
        labels: <?= json_encode($chartLabels); ?>,
        entries: <?= json_encode($entrySeries); ?>,
        exits: <?= json_encode($exitSeries); ?>,
    };
</script>
<?php
include __DIR__ . '/includes/footer.php';

