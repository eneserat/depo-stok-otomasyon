<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_auth();

$pageTitle = 'Ürünler';
$activePage = 'products';
$message = '';
$messageType = 'success';

$categoriesList = fetch_all('SELECT * FROM categories ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf($_POST['csrf_token'] ?? '');
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $data = [
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'name' => trim($_POST['name'] ?? ''),
                'sku' => trim($_POST['sku'] ?? ''),
                'quantity' => (int)($_POST['quantity'] ?? 0),
                'unit_price' => (float)($_POST['unit_price'] ?? 0),
                'reorder_point' => max(0, (int)($_POST['reorder_point'] ?? 0)),
            ];
            if ($data['name'] === '') {
                throw new RuntimeException('Ürün adı zorunludur.');
            }
            run_query(
                'INSERT INTO products (category_id, name, sku, quantity, unit_price, reorder_point) 
                 VALUES (:category_id, :name, :sku, :quantity, :unit_price, :reorder_point)',
                $data
            );
            $message = 'Ürün başarıyla eklendi.';
        } elseif ($action === 'update') {
            $data = [
                'id' => (int)($_POST['id'] ?? 0),
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'name' => trim($_POST['name'] ?? ''),
                'sku' => trim($_POST['sku'] ?? ''),
                'unit_price' => (float)($_POST['unit_price'] ?? 0),
                'reorder_point' => max(0, (int)($_POST['reorder_point'] ?? 0)),
            ];
            run_query(
                'UPDATE products SET category_id = :category_id, name = :name, sku = :sku, 
                        unit_price = :unit_price, reorder_point = :reorder_point
                 WHERE id = :id',
                $data
            );
            $message = 'Ürün güncellendi.';
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            run_query('DELETE FROM products WHERE id = :id', ['id' => $id]);
            $message = 'Ürün silindi.';
        }
    } catch (Throwable $e) {
        $messageType = 'danger';
        $message = $e->getMessage();
    }
}

$products = fetch_all(
    'SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.name'
);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Ürünler</h4>
        <p class="text-muted mb-0">Stok durumu, maliyet ve kritik limitleri tek yerden yönet.</p>
    </div>
    <button class="btn btn-primary btn-rounded" data-bs-toggle="modal" data-bs-target="#productModal" data-action="create">
        <i class="bi bi-plus-lg me-2"></i>Yeni Ürün
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType; ?>"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="card page-info-card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-muted mb-1">Ürün Kataloğu</p>
                <h5 class="mb-2">Görselli stok ve fiyat kontrolü</h5>
                <p class="mb-0">Ürünlerinizin stok seviyeleri, yeniden sipariş noktaları ve fiyat değişimlerini grafiksel olarak takip edin.</p>
            </div>
            <div class="col-lg-5 text-center">
                <img src="assets/img/operations-art.svg" alt="Ürün illüstrasyonu" class="img-fluid">
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
                    <th>Adı</th>
                    <th>Kategori</th>
                    <th>SKU</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Kritik Limit</th>
                    <th class="text-end">Birim Fiyat</th>
                    <th class="text-end">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']); ?></td>
                        <td><?= htmlspecialchars($product['category_name'] ?? 'Atanmadı'); ?></td>
                        <td><?= htmlspecialchars($product['sku']); ?></td>
                        <td class="text-center">
                            <span class="badge <?= $product['quantity'] <= $product['reorder_point'] ? 'bg-danger' : 'bg-success'; ?>">
                                <?= (int)$product['quantity']; ?>
                            </span>
                        </td>
                        <td class="text-center"><?= (int)$product['reorder_point']; ?></td>
                        <td class="text-end">₺<?= number_format((float)$product['unit_price'], 2); ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#productModal"
                                    data-action="update"
                                    data-product='<?= json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS); ?>'>Düzenle</button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$product['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Ürün</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" value="">
                <div class="col-md-6">
                    <label class="form-label">Adı</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">Atanmadı</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option value="<?= (int)$category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Başlangıç Stoğu</label>
                    <input type="number" name="quantity" class="form-control" min="0" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Birim Fiyat</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" min="0" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kritik Limit</label>
                    <input type="number" name="reorder_point" class="form-control" min="0" value="5">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
    const productModal = document.getElementById('productModal');
    if (productModal) {
        productModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const form = productModal.querySelector('form');
            form.reset();
            if (button?.dataset.action === 'update') {
                const product = JSON.parse(button.dataset.product);
                form.querySelector('input[name="action"]').value = 'update';
                form.querySelector('input[name="id"]').value = product.id;
                form.querySelector('input[name="name"]').value = product.name;
                form.querySelector('select[name="category_id"]').value = product.category_id || '';
                form.querySelector('input[name="sku"]').value = product.sku ?? '';
                form.querySelector('input[name="unit_price"]').value = product.unit_price ?? 0;
                form.querySelector('input[name="reorder_point"]').value = product.reorder_point ?? 0;
                form.querySelector('input[name="quantity"]').closest('.col-md-4').classList.add('d-none');
            } else {
                form.querySelector('input[name="action"]').value = 'create';
                form.querySelector('input[name="id"]').value = '';
                form.querySelector('input[name="quantity"]').closest('.col-md-4').classList.remove('d-none');
            }
        });
    }
</script>

<?php
include __DIR__ . '/includes/footer.php';

