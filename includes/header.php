<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

$pageTitle = $pageTitle ?? APP_NAME;
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.1.5/datatables.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="d-flex" id="app-wrapper">
    <aside class="sidebar bg-primary text-white">
        <div class="sidebar-brand text-center py-4">
            <h5 class="mb-0 fw-bold"><?= htmlspecialchars(APP_NAME); ?></h5>
            <span class="text-white-50 small">Kontrol Paneli</span>
        </div>
        <ul class="nav flex-column px-3">
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Gösterge Paneli
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'products' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box-seam me-2"></i>Ürünler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'categories' ? 'active' : ''; ?>" href="categories.php">
                    <i class="bi bi-tags me-2"></i>Kategoriler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'entries' ? 'active' : ''; ?>" href="stock_entries.php">
                    <i class="bi bi-arrow-down-circle me-2"></i>Stok Girişleri
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'exits' ? 'active' : ''; ?>" href="stock_exits.php">
                    <i class="bi bi-arrow-up-circle me-2"></i>Stok Çıkışları
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activePage === 'reports' ? 'active' : ''; ?>" href="reports.php">
                    <i class="bi bi-graph-up-arrow me-2"></i>Raporlar
                </a>
            </li>
        </ul>
    </aside>
    <div class="flex-grow-1 bg-light min-vh-100">
        <nav class="navbar navbar-expand bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-outline-primary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h6 class="mb-0 text-secondary"><?= htmlspecialchars($pageTitle); ?></h6>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">Merhaba, <?= htmlspecialchars(current_user()['username'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger">Çıkış Yap</a>
                </div>
            </div>
        </nav>
        <main class="p-4">

