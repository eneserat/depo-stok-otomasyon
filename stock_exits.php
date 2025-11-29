<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_auth();

$pageTitle = 'Stok Çıkışları';
$activePage = 'exits';
$message = '';
$messageType = 'success';

$productsList = fetch_all('SELECT id, name, quantity FROM products ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf($_POST['csrf_token'] ?? '');
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 0));
        $note = trim($_POST['note'] ?? '');

        $product = fetch_one('SELECT quantity FROM products WHERE id = :id', ['id' => $productId]);
        if (!$product || $product['quantity'] < $quantity) {
            throw new RuntimeException('Bu ürün için yeterli stok bulunmuyor.');
        }

        run_query(
            'INSERT INTO stock_exits (product_id, quantity, note, user_id) VALUES (:product_id, :quantity, :note, :user_id)',
            [
                'product_id' => $productId,
                'quantity' => $quantity,
                'note' => $note,
                'user_id' => current_user()['id'],
            ]
        );
        run_query('UPDATE products SET quantity = quantity - :qty WHERE id = :id', [
            'qty' => $quantity,
            'id' => $productId,
        ]);
        $message = 'Stok çıkışı kaydedildi.';
    } catch (Throwable $e) {
        $messageType = 'danger';
        $message = $e->getMessage();
    }
}

$exits = fetch_all(
    'SELECT sx.*, p.name AS product_name, u.username
     FROM stock_exits sx
     INNER JOIN products p ON p.id = sx.product_id
     INNER JOIN users u ON u.id = sx.user_id
     ORDER BY sx.created_at DESC'
);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Çıkan Stok</h4>
        <p class="text-muted mb-0">Sevkiyat, satış ve fire süreçlerini kaydedin.</p>
    </div>
    <button class="btn btn-danger btn-rounded" data-bs-toggle="modal" data-bs-target="#exitModal">
        <i class="bi bi-arrow-up-circle me-2"></i>Çıkış Oluştur
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType; ?>"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="card page-info-card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-muted mb-1">Çıkış Görselleştirme</p>
                <h5 class="mb-2">Sevkiyat ve tüketimleri izleyin</h5>
                <p class="mb-0">Depodan çıkan her ürün için kullanıcı, açıklama ve stok etkisini grafiksel olarak takip edin.</p>
            </div>
            <div class="col-lg-5 text-center">
                <img src="assets/img/operations-art.svg" alt="Stok çıkışı görseli" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle" data-table>
                <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Adet</th>
                    <th>Not</th>
                    <th>Kayıt Eden</th>
                    <th>Tarih</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($exits as $exit): ?>
                    <tr>
                        <td><?= htmlspecialchars($exit['product_name']); ?></td>
                        <td><span class="badge bg-danger"><?= (int)$exit['quantity']; ?></span></td>
                        <td><?= htmlspecialchars($exit['note']); ?></td>
                        <td><?= htmlspecialchars($exit['username']); ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($exit['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="exitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Stok Çıkışı Kaydet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Ürün</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Ürün seçin</option>
                        <?php foreach ($productsList as $product): ?>
                            <option value="<?= (int)$product['id']; ?>">
                                <?= htmlspecialchars($product['name']); ?> (Stok: <?= (int)$product['quantity']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adet</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Not</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Opsiyonel"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button class="btn btn-danger">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';

