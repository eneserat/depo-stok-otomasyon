# 🏭 Depo Stok Otomasyonu

Depo Stok Otomasyonu, küçük ve orta ölçekli işletmeler için geliştirilmiş modern bir **stok ve envanter yönetim sistemi**dir. PHP ve MySQL ile hazırlanmış olup, kullanıcı dostu arayüzü ile stok takibini kolaylaştırır.

---

## 🔹 Özellikler

- **Ürün Yönetimi**
  - Yeni ürün ekleme, güncelleme ve silme
  - Kategori bazlı ürün filtreleme
  - Barkod ile hızlı ürün arama

- **Stok Takibi**
  - Günlük ve haftalık stok değişikliklerini takip etme
  - Azalan stoklar için uyarı sistemi
  - Stok miktarını manuel güncelleme

- **Raporlama**
  - Ürün bazlı, kategori bazlı ve tarih bazlı raporlar
  - Excel/PDF formatında dışa aktarma desteği
  - Görsel grafiklerle stok analizi (isteğe bağlı)

- **Kullanıcı Yönetimi**
  - Admin ve personel yetkilendirmesi
  - Kullanıcı ekleme, silme ve yetki düzenleme
  - Giriş/çıkış logları ve işlem geçmişi

---

## 🛠 Teknolojiler

- **Backend:** PHP 8+  
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5  
- **Veritabanı:** MySQL  
- **Araçlar:** Git, GitHub, XAMPP/WAMP  

---

## 🚀 Kurulum

1. Projeyi klonlayın:

```bash
git clone https://github.com/eneserat/depo-stok-otomasyon.git


2 .Klasöre Geçin :

cd depo-stok-
Veritabanını oluşturun:

MySQL üzerinden yeni bir veritabanı oluşturun, örnek adı: stok_db

config.php dosyasını açıp veritabanı bilgilerini güncelleyin:
<?php
$host = "localhost";
$db   = "stok_db";
$user = "root";
$pass = "";
?>

Local server’ı başlatın (XAMPP/WAMP) ve proje dizinini çalıştırın:
http://localhost/depo-stok-otomasyon/

🎯 Kullanım

Ürün Ekle: Yeni ürün, stok ve kategori bilgilerini girin

Stok Güncelle: Mevcut ürünlerin stok miktarını güncelleyin

Raporlar: Tarih ve kategori bazlı stok durumlarını görüntüleyin

Kullanıcı Yönetimi: Personel ekleme ve yetkilendirme

Proje Sahibi: Eşref Enes Erat







