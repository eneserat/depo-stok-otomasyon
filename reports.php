<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_auth();

$pageTitle = 'Raporlar';
$activePage = 'reports';

$selectedProduct = (int)($_GET['product_id'] ?? 0);

$entryFilter = '';
$exitFilter = '';
$filterParams = [];
if ($selectedProduct > 0) {
    $entryFilter = 'WHERE se.product_id = :pid_entry';
    $exitFilter = 'WHERE sx.product_id = :pid_exit';
    $filterParams['pid_entry'] = $selectedProduct;
    $filterParams['pid_exit'] = $selectedProduct;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = fetch_all(
        "SELECT * FROM (
            SELECT p.name AS product, 'Entry' AS type, se.quantity, se.note, u.username, se.created_at
            FROM stock_entries se
            INNER JOIN products p ON p.id = se.product_id
            INNER JOIN users u ON u.id = se.user_id
            $entryFilter
            UNION ALL
            SELECT p.name AS product, 'Exit' AS type, sx.quantity, sx.note, u.username, sx.created_at
            FROM stock_exits sx
            INNER JOIN products p ON p.id = sx.product_id
            INNER JOIN users u ON u.id = sx.user_id
            $exitFilter
        ) data
        ORDER BY created_at DESC",
        $filterParams
    );
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="stock_history.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ürün', 'Tür', 'Adet', 'Not', 'Kullanıcı', 'Tarih']);
    foreach ($rows as $row) {
        $typeLabel = $row['type'] === 'Entry' ? 'Giriş' : 'Çıkış';
        fputcsv($output, [$row['product'], $typeLabel, $row['quantity'], $row['note'], $row['username'], $row['created_at']]);
    }
    fclose($output);
    exit;
}

$lowStockItems = fetch_all(
    'SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.quantity <= p.reorder_point
     ORDER BY p.quantity ASC'
);

$productsList = fetch_all('SELECT id, name FROM products ORDER BY name');

$history = fetch_all(
    "SELECT * FROM (
        SELECT se.product_id, p.name AS product, 'Entry' AS type, se.quantity, se.note, u.username, se.created_at
        FROM stock_entries se
        INNER JOIN products p ON p.id = se.product_id
        INNER JOIN users u ON u.id = se.user_id
        $entryFilter
        UNION ALL
        SELECT sx.product_id, p.name AS product, 'Exit' AS type, sx.quantity, sx.note, u.username, sx.created_at
        FROM stock_exits sx
        INNER JOIN products p ON p.id = sx.product_id
        INNER JOIN users u ON u.id = sx.user_id
        $exitFilter
    ) movements
    ORDER BY created_at DESC
    LIMIT 200",
    $filterParams
);

include __DIR__ . '/includes/header.php';
?>

<div class="card page-info-card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-muted mb-1">Rapor Görselleri</p>
                <h5 class="mb-2">Düşük stok ve geçmiş hareketleri analiz edin</h5>
                <p class="mb-0">Görsel özetler sayesinde stok risklerini, hareket trendlerini ve CSV dışa aktarımlarını tek ekrandan yönetin.</p>
            </div>
            <div class="col-lg-5 text-center">
                <img src="assets/img/operations-art.svg" alt="Rapor illüstrasyonu" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Düşük Stok Uyarıları</h5>
                        <p class="text-muted small mb-0">Kritik limitin altındaki ürünler.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-sm">
                        <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Kritik Limit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']); ?></td>
                                <td><?= htmlspecialchars($item['category_name'] ?? 'Yok'); ?></td>
                                <td><span class="badge bg-danger"><?= (int)$item['quantity']; ?></span></td>
                                <td><?= (int)$item['reorder_point']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lowStockItems)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted small">Tüm stoklar güvenli seviyede 🎉</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <h5 class="mb-3">Stok Hareket Geçmişi</h5>
                <form class="row g-2 mb-3">
                    <div class="col-md-8">
                        <select name="product_id" class="form-select" onchange="this.form.submit()">
                            <option value="0">Tüm ürünler</option>
                            <?php foreach ($productsList as $product): ?>
                                <option value="<?= (int)$product['id']; ?>" <?= $selectedProduct === (int)$product['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button type="submit" name="export" value="csv" class="btn btn-outline-primary w-100">
                            CSV Dışa Aktar
                        </button>
                    </div>
                </form>
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Tür</th>
                            <th>Adet</th>
                            <th>Kullanıcı</th>
                            <th>Tarih</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product']); ?></td>
                                <td>
                                    <?php
                                    $typeLabel = $row['type'] === 'Entry' ? 'Giriş' : 'Çıkış';
                                    $typeClass = $row['type'] === 'Entry' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?= $typeClass; ?>">
                                        <?= $typeLabel; ?>
                                    </span>
                                </td>
                                <td><?= (int)$row['quantity']; ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= date('d.m H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted small">Henüz hareket kaydı yok.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';

