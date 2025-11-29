<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (is_authenticated()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf($_POST['csrf_token'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = fetch_one('SELECT * FROM users WHERE username = :username LIMIT 1', ['username' => $username]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
            ];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Kullanıcı adı veya şifre hatalı.';
    } catch (Throwable $e) {
        $error = 'Güvenlik doğrulaması başarısız.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş | <?= htmlspecialchars(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<div class="d-flex align-items-center justify-content-center min-vh-100 px-3">
    <div class="login-wrapper">
        <div class="card login-card">
            <div class="row g-0">
                <div class="col-lg-6 p-5">
                    <div class="mb-4">
                        <span class="badge-soft">Yönetim Paneli</span>
                        <h3 class="fw-bold mt-3 mb-1"><?= htmlspecialchars(APP_NAME); ?></h3>
                        <p class="text-muted mb-0">Tüm stok ve depo süreçlerinizi tek panelden yönetin.</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger small"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()); ?>">
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" name="username" class="form-control form-control-lg" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Şifre</label>
                            <input type="password" name="password" class="form-control form-control-lg" required>
                        </div>
                        <button class="btn btn-primary w-100 btn-lg rounded-pill">Panele Giriş Yap</button>
                        <p class="text-muted text-center small mt-3 mb-0">Umarım Gününüz Güzel Geçiyordur Bugün Nasılsınız ? </p>
                    </form>
                </div>
                <div class="col-lg-6 d-none d-lg-block login-visual position-relative">
                    <div class="position-absolute bottom-0 start-0 end-0 text-white p-4"
                         style="background: linear-gradient(180deg, rgba(4,9,30,0) 0%, rgba(4,9,30,0.7) 80%);">
                        <p class="mb-1 fw-semibold">Canlı stok grafikleri • Düşük stok uyarıları</p>
                        <small class="text-white-50">Gerçek zamanlı depo içgörüleri</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

