---
description: Menambahkan kolom Barang dan Qty pada table Work Order di halaman Kadiv Mekanik
---

# Catatan Perubahan - 25 Desember 2025

## Konteks

User meminta untuk menerapkan struktur table dari file `work_order.blade.php` ke dua file lainnya:

1. `riwayat_work_order.blade.php`
2. `daftar_pengajuan_work_order.blade.php`

Tujuan utama adalah menambahkan kolom **Barang** dan **Qty** yang sebelumnya hanya ada di `work_order.blade.php`.

---

## Langkah 1: Analisis File Sumber (work_order.blade.php)

### Struktur Table Asli (11 kolom):

| No  | Kolom          | Baris di File | Data Source                                     |
| --- | -------------- | ------------- | ----------------------------------------------- |
| 1   | No             | 623           | `$i + 1` (index loop)                           |
| 2   | No Work-Order  | 624           | `$wo->no_surat_pengajuan`                       |
| 3   | Jenis WO       | 626-630       | `$wo->jenisWorkOrder->nama_jenis_wo`            |
| 4   | Divisi Pengaju | 632           | `$wo->divisi_pengaju`                           |
| 5   | Hari/Tanggal   | 633           | `$wo->tanggal` (formatted dengan Carbon)        |
| 6   | Unit/Code      | 660-670       | `$wo->unit` (conditional berdasarkan jenis WO)  |
| 7   | **Barang**     | 673-679       | Parsed dari `$wo->unit` (untuk jenis Pembelian) |
| 8   | **Qty**        | 682-688       | Parsed dari `$wo->unit` (untuk jenis Pembelian) |
| 9   | Uraian         | 689           | `$wo->uraian` (truncated 30 chars)              |
| 10  | Status         | 690-698       | `$wo->verifikator->nama_status`                 |
| 11  | Aksi           | 699-736       | Tombol View, Edit, Delete, Kirim Ulang          |

### Logic Penting yang Ditemukan:

```php
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
```

---

## Langkah 2: Perubahan pada riwayat_work_order.blade.php

### Lokasi File:

`resources/views/kadivmekanik/riwayat_work_order.blade.php`

### Perubahan 1: Table Header (Baris 223-235)

**SEBELUM:**

```html
<thead class="thead-dark">
    <tr>
        <th>No</th>
        <th>No Work-Order</th>
        <th>Jenis WO</th>
        <th>Divisi Pengaju</th>
        <th>Hari/Tanggal</th>
        <th>Unit/Code</th>
        <th>Uraian</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
</thead>
```

**SESUDAH:**

```html
<thead class="thead-dark">
    <tr>
        <th>No</th>
        <th>No Work-Order</th>
        <th>Jenis WO</th>
        <th>Divisi Pengaju</th>
        <th>Hari/Tanggal</th>
        <th>Unit/Code</th>
        <th>Barang</th>
        <!-- BARU -->
        <th>Qty</th>
        <!-- BARU -->
        <th>Uraian</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
</thead>
```

### Perubahan 2: Table Body (Baris 236-270)

**SEBELUM:**

```blade
<tbody>
    @forelse($workOrders as $i => $wo)
        <tr>
            <td></td>
            <td>{{ $wo->no_surat_pengajuan }}</td>
            <td>
                @if($wo->jenisWorkOrder)
                    {{ $wo->jenisWorkOrder->nama_jenis_wo }}
                @else
                    -
                @endif
            </td>
            <td>{{ $wo->divisi_pengaju }}</td>
            <td data-order="...">{{ ... }}</td>
            <td>{{ $wo->unit_code ?? $wo->unit }}</td>
            <td>{{ Str::limit($wo->uraian, 30) }}</td>
            <td>
                @php
                    $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                @endphp
                @if($status == 'Disetujui' || $status == 'Selesai')
                    <span class="badge badge-success">{{ $status }}</span>
                @elseif($status == 'Ditolak')
                    <span class="badge badge-danger">{{ $status }}</span>
                @else
                    <span class="badge badge-warning">{{ $status }}</span>
                @endif
            </td>
            <td>...</td>
        </tr>
```

**SESUDAH:**

```blade
<tbody>
    @forelse($workOrders as $i => $wo)
        @php
            $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
            $jenisWo = $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '';
            $isPembelian = $jenisWo === 'pembelian';
            $isPermintaan = $jenisWo === 'permintaan';
            $isPerbaikan = $jenisWo === 'perbaikan';

            // Parse unit field untuk mendapatkan barang dan qty
            $barangItems = [];
            $qtyItems = [];

            if ($isPembelian && $wo->unit && $wo->unit !== '-') {
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
        <tr>
            <td></td>
            <td>{{ $wo->no_surat_pengajuan }}</td>
            <td>...</td>
            <td>{{ $wo->divisi_pengaju }}</td>
            <td data-order="...">{{ ... }}</td>

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

            {{-- Kolom Barang (BARU) --}}
            <td>
                @if($isPembelian && count($barangItems) > 0)
                    {{ implode(', ', $barangItems) }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            {{-- Kolom Qty (BARU) --}}
            <td>
                @if($isPembelian && count($qtyItems) > 0)
                    {{ implode(', ', $qtyItems) }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            <td>{{ Str::limit($wo->uraian, 30) }}</td>
            <td>
                @if($status == 'Disetujui' || $status == 'Selesai')
                    <span class="badge badge-success">{{ $status }}</span>
                @elseif(Str::contains($status, 'Ditolak'))  <!-- DIPERBAIKI -->
                    <span class="badge badge-danger">{{ $status }}</span>
                @else
                    <span class="badge badge-warning">{{ $status }}</span>
                @endif
            </td>
            <td>...</td>
        </tr>
```

### Perubahan 3: Filter Status JavaScript (Baris 404-416)

**SEBELUM:**

```javascript
// Kolom Status adalah index 7
if (status === "") {
    table.column(7).search("").draw();
} else if (status === "Disetujui") {
    table.column(7).search("(Disetujui|Selesai)", true, false).draw();
} else {
    table.column(7).search(status, true, false).draw();
}
```

**SESUDAH:**

```javascript
// Kolom Status adalah index 9 (setelah penambahan 2 kolom baru)
if (status === "") {
    table.column(9).search("").draw();
} else if (status === "Disetujui") {
    table.column(9).search("(Disetujui|Selesai)", true, false).draw();
} else {
    table.column(9).search(status, true, false).draw();
}
```

---

## Langkah 3: Perubahan pada daftar_pengajuan_work_order.blade.php

### Lokasi File:

`resources/views/kadivmekanik/daftar_pengajuan_work_order.blade.php`

### Perubahan 1: Table Header (Baris 27-39)

**SEBELUM:**

```html
<thead class="thead-dark">
    <tr>
        <th>No</th>
        <th>No Work-Order</th>
        <th>Jenis WO</th>
        <th>Divisi Pengaju</th>
        <th>Hari/Tanggal</th>
        <th>Unit/Code</th>
        <th>Uraian</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
</thead>
```

**SESUDAH:**

```html
<thead class="thead-dark">
    <tr>
        <th>No</th>
        <th>No Work-Order</th>
        <th>Jenis WO</th>
        <th>Divisi Pengaju</th>
        <th>Hari/Tanggal</th>
        <th>Unit/Code</th>
        <th>Barang</th>
        <!-- BARU -->
        <th>Qty</th>
        <!-- BARU -->
        <th>Uraian</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
</thead>
```

### Perubahan 2: Table Body (Baris 40-67)

**SEBELUM:**

```blade
<tbody>
    @forelse($submissionWorkOrders as $i => $wo)
        @php
            $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $wo->no_surat_pengajuan }}</td>
            <td>...</td>
            <td>{{ $wo->divisi_pengaju }}</td>
            <td>{{ ... }}</td>
            <td>{{ $wo->unit }}</td>
            <td>{{ Str::limit($wo->uraian, 30) }}</td>
            <td>
                @if($status == 'Disetujui' || $status == 'Selesai')
                    <span class="badge badge-success">{{ $status }}</span>
                @elseif($status == 'Ditolak')
                    <span class="badge badge-danger">{{ $status }}</span>
                @else
                    <span class="badge badge-warning">{{ $status }}</span>
                @endif
            </td>
```

**SESUDAH:**

```blade
<tbody>
    @forelse($submissionWorkOrders as $i => $wo)
        @php
            $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
            $jenisWo = $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '';
            $isPembelian = $jenisWo === 'pembelian';
            $isPermintaan = $jenisWo === 'permintaan';
            $isPerbaikan = $jenisWo === 'perbaikan';

            // Parse unit field untuk mendapatkan barang dan qty
            $barangItems = [];
            $qtyItems = [];

            if ($isPembelian && $wo->unit && $wo->unit !== '-') {
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
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $wo->no_surat_pengajuan }}</td>
            <td>...</td>
            <td>{{ $wo->divisi_pengaju }}</td>
            <td>{{ ... }}</td>

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

            {{-- Kolom Barang (BARU) --}}
            <td>
                @if($isPembelian && count($barangItems) > 0)
                    {{ implode(', ', $barangItems) }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            {{-- Kolom Qty (BARU) --}}
            <td>
                @if($isPembelian && count($qtyItems) > 0)
                    {{ implode(', ', $qtyItems) }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            <td>{{ Str::limit($wo->uraian, 30) }}</td>
            <td>
                @if($status == 'Disetujui' || $status == 'Selesai')
                    <span class="badge badge-success">{{ $status }}</span>
                @elseif(Str::contains($status, 'Ditolak'))  <!-- DIPERBAIKI -->
                    <span class="badge badge-danger">{{ $status }}</span>
                @else
                    <span class="badge badge-warning">{{ $status }}</span>
                @endif
            </td>
```

---

## Ringkasan Perubahan

### File yang Dimodifikasi:

1. `resources/views/kadivmekanik/riwayat_work_order.blade.php`
2. `resources/views/kadivmekanik/daftar_pengajuan_work_order.blade.php`

### Kolom yang Ditambahkan:

-   **Barang** - Menampilkan nama barang yang di-parse dari field `unit` untuk WO jenis Pembelian
-   **Qty** - Menampilkan jumlah qty dari field `unit` untuk WO jenis Pembelian

### Logic Tambahan:

1. Parsing regex untuk extract nama barang dan qty dari format: `"NamaBarang (qty: X), NamaBarang2 (qty: Y)"`
2. Conditional display berdasarkan jenis Work Order (Pembelian, Permintaan, Perbaikan)
3. Update index kolom status pada filter JavaScript di riwayat_work_order.blade.php (dari 7 ke 9)

### Bug Fix:

-   Mengubah kondisi `$status == 'Ditolak'` menjadi `Str::contains($status, 'Ditolak')` untuk menangani semua variasi status ditolak seperti "Ditolak Atasan"

### Struktur Table Baru (11 kolom):

| No  | No Work-Order | Jenis WO | Divisi Pengaju | Hari/Tanggal | Unit/Code | Barang | Qty | Uraian | Status | Aksi |
| --- | ------------- | -------- | -------------- | ------------ | --------- | ------ | --- | ------ | ------ | ---- |

---

## Catatan Teknis

### Regex Pattern yang Digunakan:

```php
'/^(.+?)\s*\(qty:\s*(\d+)\)$/i'
```

-   `^(.+?)` - Capture group 1: Nama barang (non-greedy)
-   `\s*` - Spasi opsional
-   `\(qty:\s*` - Literal "(qty:" dengan spasi opsional
-   `(\d+)` - Capture group 2: Angka qty
-   `\)$` - Literal ")" di akhir
-   `/i` - Case insensitive

### Format Data yang Di-parse:

-   Input: `"Baut M10 (qty: 5), Mur M10 (qty: 10), Oli Mesin (qty: 2)"`
-   Output Barang: `["Baut M10", "Mur M10", "Oli Mesin"]`
-   Output Qty: `[5, 10, 2]`

---

_Dokumentasi dibuat: 25 Desember 2025, 12:34 WIB_
