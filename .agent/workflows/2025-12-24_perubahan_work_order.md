---
description: Dokumentasi perubahan pada sistem Work Order - 24 Desember 2025
---

# Catatan Perubahan - 24 Desember 2025

## Ringkasan

Perubahan meliputi pembuatan tabel pivot untuk relasi many-to-many surat pengajuan, penyembunyian field unit untuk jenis WO Permintaan, dan penambahan kolom Barang serta Qty pada tabel daftar Work Order.

---

## 1. Tabel Pivot `surat_pengajuan_refrensi` (Many-to-Many)

### 1.1 Migration

**File:** `database/migrations/2025_12_24_211213_surat_pengajuan_refrensi.php`

**Perubahan:** Membuat tabel pivot untuk relasi many-to-many antar surat pengajuan.

**Struktur Tabel:**

```php
Schema::create('surat_pengajuan_refrensi', function (Blueprint $table) {
    $table->id('id_surat_pengajuan_refrensi');
    $table->unsignedBigInteger('id_surat_pengajuan');
    $table->unsignedBigInteger('id_surat_pengajuan_referensi');
    $table->timestamps();

    // Foreign key ke surat_pengajuan (surat utama)
    $table->foreign('id_surat_pengajuan')
          ->references('id_surat_pengajuan')
          ->on('surat_pengajuan')
          ->onDelete('cascade')
          ->onUpdate('cascade');

    // Foreign key ke surat_pengajuan (surat referensi)
    $table->foreign('id_surat_pengajuan_referensi')
          ->references('id_surat_pengajuan')
          ->on('surat_pengajuan')
          ->onDelete('cascade')
          ->onUpdate('cascade');
});
```

### 1.2 Model SuratPengajuanRefrensi (BARU)

**File:** `app/Models/SuratPengajuanRefrensi.php`

**Isi File:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPengajuanRefrensi extends Model
{
    protected $table = 'surat_pengajuan_refrensi';
    protected $primaryKey = 'id_surat_pengajuan_refrensi';
    public $timestamps = true;

    protected $fillable = [
        'id_surat_pengajuan',
        'id_surat_pengajuan_referensi',
    ];

    public function suratPengajuan()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan', 'id_surat_pengajuan');
    }

    public function suratPengajuanReferensi()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan_referensi', 'id_surat_pengajuan');
    }
}
```

### 1.3 Update Model SuratPengajuan

**File:** `app/Models/SuratPengajuan.php`

**Perubahan:** Menambahkan 2 relasi belongsToMany di akhir class:

```php
/**
 * Relasi many-to-many ke surat pengajuan yang menjadi referensi
 */
public function referensi()
{
    return $this->belongsToMany(
        SuratPengajuan::class,
        'surat_pengajuan_refrensi',
        'id_surat_pengajuan',
        'id_surat_pengajuan_referensi'
    )->withTimestamps();
}

/**
 * Relasi many-to-many ke surat pengajuan yang mereferensikan surat ini
 */
public function direferensiOleh()
{
    return $this->belongsToMany(
        SuratPengajuan::class,
        'surat_pengajuan_refrensi',
        'id_surat_pengajuan_referensi',
        'id_surat_pengajuan'
    )->withTimestamps();
}
```

### 1.4 Seeder (BARU)

**File:** `database/seeders/SuratPengajuanRefrensiSeeder.php`

**Fungsi:** Membuat contoh data relasi referensi antar surat pengajuan.

**Hasil:** 10 relasi referensi dibuat.

---

## 2. Menyembunyikan Field Unit/Code untuk Jenis WO "Permintaan"

### 2.1 Menambahkan ID pada Row Unit

**File:** `resources/views/kadivmekanik/work_order.blade.php`

**Perubahan (Baris ~788):**

```html
<!-- Sebelum -->
<div class="row">
    <!-- Sesudah -->
    <div class="row" id="unit_row"></div>
</div>
```

**Perubahan (Baris ~952):**

```html
<!-- Sebelum (Modal Edit) -->
<div class="row">
    <!-- Sesudah -->
    <div class="row" id="edit_unit_row"></div>
</div>
```

### 2.2 Modifikasi Fungsi JavaScript togglePerbaikanUnit()

**File:** `resources/views/kadivmekanik/work_order.blade.php`

**Lokasi:** Sekitar baris 1152-1200

**Perubahan:**

```javascript
function togglePerbaikanUnit(isEdit = false) {
    const prefix = isEdit ? "edit_" : "";
    const jenisWoSelect = $(`#${prefix}id_jenis_wo`);
    const selectedJenisWo =
        jenisWoSelect.find("option:selected").data("nama-jenis") ||
        jenisWoSelect.find("option:selected").text();
    const isPerbaikan =
        selectedJenisWo && selectedJenisWo.toLowerCase() === "perbaikan";
    const isPermintaan =
        selectedJenisWo && selectedJenisWo.toLowerCase() === "permintaan";
    const isPembelian =
        selectedJenisWo && selectedJenisWo.toLowerCase() === "pembelian";

    const toggle = $(`#${prefix}perbaikan_unit_toggle`);
    const unitSelect = $(`#${prefix}unit_select`);
    const unitInput = $(`#${prefix}unit`);
    const unitRow = $(`#${prefix}unit_row`);

    // Sembunyikan seluruh row unit jika jenis WO = Permintaan
    if (isPermintaan) {
        unitRow.hide();
        unitInput.val("").removeAttr("name").removeAttr("required");
        unitSelect.val("").removeAttr("name").removeAttr("required");
        $(`#${prefix}unit_pembelian_container`).hide().html("");
        toggle.hide();
        if (unitSelect.hasClass("select2-hidden-accessible")) {
            unitSelect.select2("destroy");
        }

        // Tambahkan hidden input untuk mengirim nilai default "-" ke server
        const hiddenInputId = `${prefix}unit_hidden`;
        if ($(`#${hiddenInputId}`).length === 0) {
            unitRow.after(
                `<input type="hidden" id="${hiddenInputId}" name="unit" value="-">`
            );
        }
        return;
    }

    // Hapus hidden input jika ada (untuk jenis WO selain Permintaan)
    $(`#${prefix}unit_hidden`).remove();

    // Tampilkan row unit untuk jenis WO lainnya
    unitRow.show();

    // ... kode lainnya tetap sama
}
```

---

## 3. Menambahkan Kolom "Barang" dan "Qty" di Tabel Daftar Work Order

### 3.1 Update Controller - Eager Loading

**File:** `app/Http/Controllers/KadivMekanikController.php`

**Lokasi:** Fungsi `workOrder()` sekitar baris 114

**Perubahan:**

```php
// Sebelum
$workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])

// Sesudah
$workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'permintaanBarang.daftarBarang'])
```

### 3.2 Menambahkan Header Kolom

**File:** `resources/views/kadivmekanik/work_order.blade.php`

**Lokasi:** Sekitar baris 606-611

**Perubahan:**

```html
<th>Hari/Tanggal</th>
<th>Unit/Code</th>
<th>Barang</th>
<!-- BARU -->
<th>Qty</th>
<!-- BARU -->
<th>Uraian</th>
<th>Status</th>
<th>Aksi</th>
```

### 3.3 Logika Tampilan Kolom Unit/Code, Barang, Qty

**File:** `resources/views/kadivmekanik/work_order.blade.php`

**Lokasi:** Sekitar baris 634-690

**Perubahan Detail:**

```blade
@php
    $jenisWo = $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '';
    $isPembelian = $jenisWo === 'pembelian';
    $isPermintaan = $jenisWo === 'permintaan';
    $isPerbaikan = $jenisWo === 'perbaikan';

    // Parse unit field untuk mendapatkan barang dan qty jika dari Pembelian
    $barangItems = [];
    $qtyItems = [];

    if ($isPembelian && $wo->unit && $wo->unit !== '-') {
        // Parse format: "NamaBarang (qty: X), NamaBarang2 (qty: Y)"
        $parts = explode(', ', $wo->unit);
        foreach ($parts as $part) {
            if (preg_match('/^(.+?)\s*\(qty:\s*(\d+)\)$/i', trim($part), $matches)) {
                $barangItems[] = trim($matches[1]);
                $qtyItems[] = (int)$matches[2];
            } elseif (!empty(trim($part))) {
                $barangItems[] = trim($part);
                $qtyItems[] = 1;
            }
        }
    }
@endphp

{{-- Kolom Unit/Code --}}
<td>
    @if($isPembelian)
        <span class="text-muted">-</span>
    @elseif($isPermintaan)
        <span class="text-muted">-</span>
    @elseif($isPerbaikan)
        {{ $wo->unit && $wo->unit !== '-' ? $wo->unit : '-' }}
    @else
        {{ $wo->unit ?? '-' }}
    @endif
</td>

{{-- Kolom Barang --}}
<td>
    @if($isPembelian && count($barangItems) > 0)
        {{ implode(', ', $barangItems) }}
    @else
        <span class="text-muted">-</span>
    @endif
</td>

{{-- Kolom Qty --}}
<td>
    @if($isPembelian && count($qtyItems) > 0)
        {{ implode(', ', $qtyItems) }}
    @else
        <span class="text-muted">-</span>
    @endif
</td>
```

---

## 4. Tabel Ringkasan Tampilan Berdasarkan Jenis WO

| Jenis WO       | Unit/Code | Barang                     | Qty                   |
| -------------- | --------- | -------------------------- | --------------------- |
| **Pembelian**  | `-`       | Nama barang (dipisah koma) | Jumlah (dipisah koma) |
| **Permintaan** | `-`       | `-`                        | `-`                   |
| **Perbaikan**  | Nama unit | `-`                        | `-`                   |

---

## 5. Perintah yang Dijalankan

```bash
# Menjalankan migration
php artisan migrate

# Menjalankan seeder
php artisan db:seed --class=SuratPengajuanRefrensiSeeder
```

---

## 6. Daftar File yang Dibuat/Dimodifikasi

### File Baru:

1. `app/Models/SuratPengajuanRefrensi.php`
2. `database/seeders/SuratPengajuanRefrensiSeeder.php`

### File yang Dimodifikasi:

1. `database/migrations/2025_12_24_211213_surat_pengajuan_refrensi.php`
2. `app/Models/SuratPengajuan.php` (menambah relasi)
3. `app/Http/Controllers/KadivMekanikController.php` (eager loading)
4. `resources/views/kadivmekanik/work_order.blade.php` (UI & JavaScript)

---

## 7. Catatan Penting

1. **Hidden Input untuk Permintaan**: Ketika jenis WO "Permintaan" dipilih, hidden input dengan nilai "-" ditambahkan agar form tetap bisa submit (field `unit` required di controller).

2. **Parsing Format Barang**: Field `unit` untuk Pembelian disimpan dengan format `"NamaBarang (qty: X), NamaBarang2 (qty: Y)"` dan di-parse untuk ditampilkan di kolom terpisah.

3. **Relasi Many-to-Many**: Gunakan `$surat->referensi` untuk mendapatkan surat referensi dan `$surat->direferensiOleh` untuk surat yang mereferensikan.
