<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_auth();

$pageTitle = 'Kategoriler';
$activePage = 'categories';
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf($_POST['csrf_token'] ?? '');
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($name === '') {
                throw new RuntimeException('Kategori adı zorunludur.');
            }
            run_query('INSERT INTO categories (name, description) VALUES (:name, :description)', [
                'name' => $name,
                'description' => $description,
            ]);
            $message = 'Kategori başarıyla oluşturuldu.';
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            run_query('UPDATE categories SET name = :name, description = :description WHERE id = :id', [
                'name' => $name,
                'description' => $description,
                'id' => $id,
            ]);
            $message = 'Kategori güncellendi.';
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            run_query('DELETE FROM categories WHERE id = :id', ['id' => $id]);
            $message = 'Kategori silindi.';
        }
    } catch (Throwable $e) {
        $messageType = 'danger';
        $message = $e->getMessage();
    }
}

$categories = fetch_all(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Kategoriler</h4>
        <p class="text-muted mb-0">Ürünlerinizi gruplandırın, stok raporlarını sadeleştirin.</p>
    </div>
    <button class="btn btn-primary btn-rounded" data-bs-toggle="modal" data-bs-target="#categoryModal" data-action="create">
        <i class="bi bi-plus-lg me-2"></i>Yeni Kategori
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType; ?>"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="card page-info-card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-muted mb-1">Görsel Entegrasyon</p>
                <h5 class="mb-2">Kategori bazlı stok analizi</h5>
                <p class="mb-0">Her kategori için stok kapasitesini, dönüş hızını ve kritik seviyeleri takip ederek tedarik planını optimize edin.</p>
            </div>
            <div class="col-lg-5 text-center">
                <img src="assets/img/operations-art.svg" alt="Kategori illüstrasyonu" class="img-fluid">
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
                    <th>Açıklama</th>
                    <th class="text-center">Ürün</th>
                    <th class="text-end">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['name']); ?></td>
                        <td><?= htmlspecialchars($category['description']); ?></td>
                        <td class="text-center"><?= (int)$category['product_count']; ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal"
                                    data-bs-target="#categoryModal"
                                    data-action="update"
                                    data-category='<?= json_encode($category, JSON_HEX_TAG | JSON_HEX_APOS); ?>'>
                                Düzenle
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$category['id']; ?>">
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

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" value="">
                <div class="mb-3">
                    <label class="form-label">Adı</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
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
    const categoryModal = document.getElementById('categoryModal');
    if (categoryModal) {
        categoryModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const form = categoryModal.querySelector('form');
            form.reset();
            if (button?.dataset.action === 'update') {
                form.querySelector('input[name="action"]').value = 'update';
                const category = JSON.parse(button.dataset.category);
                form.querySelector('input[name="id"]').value = category.id;
                form.querySelector('input[name="name"]').value = category.name;
                form.querySelector('textarea[name="description"]').value = category.description ?? '';
            } else {
                form.querySelector('input[name="action"]').value = 'create';
                form.querySelector('input[name="id"]').value = '';
            }
        });
    }
</script>

<?php
include __DIR__ . '/includes/footer.php';

