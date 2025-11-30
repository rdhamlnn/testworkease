# 📋 PANDUAN SETUP SISTEM WORK ORDER

## ✅ LANGKAH-LANGKAH SETUP

### 1. Jalankan Migration dan Seeder

```bash
php artisan migrate
php artisan db:seed
```

Atau jika ingin menjalankan seeder tertentu saja:

```bash
php artisan db:seed --class=KaryawanSeeder
php artisan db:seed --class=AkunSeeder
```

Migration yang akan dijalankan:

-   `permintaan_barang` - Membuat tabel `permintaan_barang`

Seeder yang akan dijalankan:

-   `KaryawanSeeder` - Membuat karyawan untuk semua divisi (Admin, Mekanik, Logistik, Purchasing, Produksi, Plasma, QC)
-   `AkunSeeder` - Membuat akun untuk semua user dengan password: `password`

### 2. Buat Akun Default untuk User Baru (Opsional - Sudah Ada di Seeder)

Jika ingin membuat akun default untuk testing, akses URL berikut setelah migration:

```bash
# Buat akun Logistik
http://localhost/create-default-logistik

# Buat akun Purchasing
http://localhost/create-default-purchasing

# Buat akun Atasan
http://localhost/create-default-atasan

# Buat akun Kadiv Produksi
http://localhost/create-default-kadiv-produksi

# Buat akun Kadiv Plasma
http://localhost/create-default-kadiv-plasma

# Buat akun Kadiv QC
http://localhost/create-default-kadiv-qc
```

**Credentials untuk Login (semua menggunakan password: `password`):**

| User           | Email                 | Password |
| -------------- | --------------------- | -------- |
| Admin          | admin@kce.com         | password |
| Kadiv Mekanik  | kadivmekanik@kce.com  | password |
| Mekanik        | mekanik@kce.com       | password |
| Logistik       | logistik@kce.com      | password |
| Purchasing     | purchasing@kce.com    | password |
| Atasan         | atasan@kce.com        | password |
| Kadiv Produksi | kadivproduksi@kce.com | password |
| Kadiv Plasma   | kadivplasma@kce.com   | password |
| Kadiv QC       | kadivqc@kce.com       | password |

### 3. Update Data Akun di Database (Jika Membuat Manual - Tidak Perlu Jika Pakai Seeder)

Jika membuat akun secara manual, pastikan setiap user memiliki `id_divisi` yang sesuai dengan perannya:

**Untuk Logistik:**

```sql
UPDATE akun SET id_divisi = 1, id_peran = 2 WHERE id_akun = [ID_AKUN_LOGISTIK];
-- id_divisi = 1 adalah divisi "Logistik"
```

**Untuk Purchasing:**

```sql
UPDATE akun SET id_divisi = 5, id_peran = 2 WHERE id_akun = [ID_AKUN_PURCHASING];
-- id_divisi = 5 adalah divisi "Purchasing"
```

**Untuk Atasan:**

```sql
UPDATE akun SET id_divisi = 7, id_peran = 1 WHERE id_akun = [ID_AKUN_ATASAN];
-- id_divisi = 7 adalah divisi "Administrator"
```

**Untuk Kadiv Produksi:**

```sql
UPDATE akun SET id_divisi = 3, id_peran = 2 WHERE id_akun = [ID_AKUN_KADIV_PRODUKSI];
-- id_divisi = 3 adalah divisi "Produksi"
```

**Untuk Kadiv Plasma:**

```sql
UPDATE akun SET id_divisi = 4, id_peran = 2 WHERE id_akun = [ID_AKUN_KADIV_PLASMA];
-- id_divisi = 4 adalah divisi "Plasma"
```

**Untuk Kadiv QC:**

```sql
UPDATE akun SET id_divisi = 6, id_peran = 2 WHERE id_akun = [ID_AKUN_KADIV_QC];
-- id_divisi = 6 adalah divisi "Quality Control"
```

**Catatan:**

-   Sistem menggunakan kombinasi `id_peran` + `id_divisi` untuk membedakan user
-   Logistik dan Purchasing menggunakan `id_peran = 2` (kadiv) dengan `id_divisi` berbeda
-   Atasan menggunakan `id_peran = 1` (admin) dengan `id_divisi = 7` (Administrator)
-   Pastikan `id_divisi` dan `id_karyawan` sudah terisi dengan benar
-   Mapping Divisi:
    -   `id_divisi = 1` → Logistik
    -   `id_divisi = 2` → Mekanik (untuk Kadiv Mekanik)
    -   `id_divisi = 3` → Produksi
    -   `id_divisi = 4` → Plasma
    -   `id_divisi = 5` → Purchasing
    -   `id_divisi = 6` → Quality Control
    -   `id_divisi = 7` → Administrator (untuk Atasan)

### 4. Verifikasi Routes

Pastikan semua routes terdaftar dengan benar:

```bash
php artisan route:list
```

Routes yang harus ada:

-   ✅ `logistik.*` - Dashboard, Work Order Dicek, Permintaan Barang, Terima Barang, Serahkan Barang, Profile
-   ✅ `purchasing.*` - Dashboard, Permintaan Barang, Beli Barang, Kirim Barang, Profile
-   ✅ `atasan.*` - Dashboard, Approval Permintaan, Riwayat Approval, Profile
-   ✅ `kadivproduksi.*` - Dashboard, Work Order, Daftar Work Order, Riwayat Work Order, Profile
-   ✅ `kadivplasma.*` - Dashboard, Work Order, Daftar Work Order, Riwayat Work Order, Profile
-   ✅ `kadivqc.*` - Dashboard, Work Order, Daftar Work Order, Riwayat Work Order, Profile

### 5. Clear Cache (Opsional)

Jika ada masalah, clear cache:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🔄 ALUR KERJA WORK ORDER & PERMINTAAN BARANG

### Alur Lengkap:

1. **Divisi Membuat Work Order**

    - Setiap divisi (Mekanik, Produksi, Plasma, QC, dll) dapat membuat Work Order
    - Work Order dapat ditujukan ke divisi lain sesuai kebutuhan

2. **Logistik Cek Work Order**

    - Logistik melihat semua Work Order di menu "Work Order Dicek"
    - Logistik mengecek apakah barang/material yang diperlukan tersedia
    - Jika tidak tersedia, Logistik membuat "Permintaan Barang"

3. **Permintaan Barang ke Purchasing**

    - Permintaan Barang dengan status "Menunggu Purchasing"
    - Purchasing melihat di menu "Permintaan Barang"
    - Purchasing mengirim permintaan ke Atasan untuk approval

4. **Atasan Approval**

    - Atasan melihat permintaan di menu "Approval Permintaan"
    - Atasan dapat **Setujui** atau **Tolak** permintaan
    - Jika disetujui, status menjadi "Disetujui Atasan"
    - Jika ditolak, status menjadi "Ditolak Atasan"

5. **Purchasing Beli Barang**

    - Setelah approval, Purchasing melihat di menu "Beli Barang"
    - Purchasing konfirmasi pembelian → Status "Dibeli Purchasing"

6. **Purchasing Kirim ke Logistik**

    - Purchasing melihat di menu "Kirim Barang"
    - Purchasing konfirmasi pengiriman → Status "Dikirim Purchasing"

7. **Logistik Terima Barang**

    - Logistik melihat di menu "Terima Barang"
    - Logistik konfirmasi penerimaan → Status "Diterima Logistik"

8. **Logistik Serahkan ke Divisi**
    - Logistik melihat di menu "Serahkan Barang"
    - Logistik konfirmasi penyerahan → Status "Diserahkan ke Divisi"
    - Barang siap digunakan oleh divisi yang meminta

---

## 📊 STATUS PERMINTAAN BARANG

Status yang tersedia:

-   `Menunggu Logistik` - Default status
-   `Menunggu Purchasing` - Setelah Logistik membuat permintaan
-   `Menunggu Approval Atasan` - Setelah Purchasing kirim ke Atasan
-   `Disetujui Atasan` - Setelah Atasan approve
-   `Ditolak Atasan` - Setelah Atasan reject
-   `Dibeli Purchasing` - Setelah Purchasing beli
-   `Dikirim Purchasing` - Setelah Purchasing kirim
-   `Diterima Logistik` - Setelah Logistik terima
-   `Diserahkan ke Divisi` - Setelah Logistik serahkan ke divisi

---

## 🎯 MENU UNTUK SETIAP USER

### Logistik

-   Dashboard
-   Work Order → Work Order Dicek
-   Permintaan Barang
-   Barang → Terima Barang, Serahkan Barang
-   Profile

### Purchasing

-   Dashboard
-   Permintaan Barang
-   Pembelian → Beli Barang, Kirim Barang
-   Profile

### Atasan

-   Dashboard
-   Approval → Approval Permintaan, Riwayat Approval
-   Profile

### Kadiv Produksi/Plasma/QC

-   Dashboard
-   Work Order → Work Order, Daftar Work Order, Riwayat Work Order
-   Profile

---

## ⚠️ TROUBLESHOOTING

### Masalah: User tidak bisa login atau redirect salah

**Solusi:** Pastikan `id_divisi` dan `id_peran` sudah diisi dengan benar di database sesuai dengan mapping divisi

### Masalah: Route tidak ditemukan

**Solusi:**

```bash
php artisan route:clear
php artisan config:clear
```

### Masalah: Error saat menyimpan permintaan barang

**Solusi:** Pastikan format JSON untuk `daftar_barang` valid

### Masalah: Work Order tidak muncul di Logistik

**Solusi:** Pastikan Work Order sudah di-approve (status = 'Disetujui' atau 'Selesai')

---

## 📝 CATATAN PENTING

1. **Work Order** harus di-approve terlebih dahulu sebelum Logistik bisa membuat Permintaan Barang
2. **Permintaan Barang** harus melalui approval Atasan sebelum bisa dibeli
3. **Status** harus diupdate secara berurutan sesuai alur kerja
4. Semua user harus memiliki `id_divisi` dan `id_peran` yang sesuai dengan perannya
5. Pastikan `id_karyawan`, `id_divisi`, dan `id_peran` sudah terisi untuk setiap akun
6. Sistem menggunakan kombinasi `id_peran` + `id_divisi` untuk routing, tidak perlu kolom tambahan

---

## ✅ CHECKLIST SETUP

-   [ ] Migration berhasil dijalankan
-   [ ] `id_divisi` dan `id_peran` sudah diisi dengan benar untuk semua user baru
-   [ ] Routes sudah terdaftar (cek dengan `php artisan route:list`)
-   [ ] User bisa login dengan role baru
-   [ ] Dashboard untuk setiap user baru sudah muncul
-   [ ] Work Order bisa dibuat oleh Kadiv
-   [ ] Logistik bisa melihat Work Order
-   [ ] Permintaan Barang bisa dibuat oleh Logistik
-   [ ] Purchasing bisa melihat dan kirim ke Atasan
-   [ ] Atasan bisa approve/reject
-   [ ] Alur lengkap sampai barang diserahkan berjalan

---

**Sistem siap digunakan! 🎉**
