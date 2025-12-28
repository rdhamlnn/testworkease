<!-- Modal Konfirmasi Delete -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus Data
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="text-center mb-3" id="deleteConfirmMessage">
                    Apakah Anda yakin ingin menghapus data ini?
                </h5>
                <p class="text-center text-muted mb-0">
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <form id="deleteConfirmForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Pastikan jQuery tersedia sebelum menjalankan script
(function($) {
    'use strict';
    
    function isLocked(element) {
        const raw = $(element).data('locked');
        return raw === true || raw === 'true';
    }

    function showLockWarning(message) {
        const finalMessage = message || 'Data ini tidak dapat dihapus karena status saat ini.';
        if (typeof window.triggerToast === 'function') {
            window.triggerToast(finalMessage, 'danger', 5200);
        } else {
            alert(finalMessage);
        }
    }

    // Fungsi untuk menampilkan modal konfirmasi delete
    function showDeleteConfirm(url, message) {
        const modal = $('#deleteConfirmModal');
        const form = $('#deleteConfirmForm');
        const messageEl = $('#deleteConfirmMessage');
        
        // Set pesan konfirmasi
        if (message) {
            messageEl.text(message);
        } else {
            messageEl.text('Apakah Anda yakin ingin menghapus data ini?');
        }
        
        // Set action form
        form.attr('action', url);
        
        // Tampilkan modal
        modal.modal('show');
    }
    
    // Pastikan fungsi tersedia secara global
    window.showDeleteConfirm = showDeleteConfirm;
    
    // Tunggu sampai DOM ready
    $(document).ready(function() {
        // Event handler untuk tombol delete dengan class .btn-delete
        // Gunakan .off() untuk mencegah duplicate handlers
        $(document).off('click', '.btn-delete').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (isLocked(this)) {
                showLockWarning($(this).data('lockMessage'));
                return;
            }

            const url = $(this).data('url') || $(this).closest('form').attr('action');
            const message = $(this).data('message');
            
            console.log('Delete button clicked, url:', url, 'message:', message); // Debug
            
            if (url) {
                showDeleteConfirm(url, message);
            } else {
                console.error('Delete button tidak memiliki data-url atau form action');
            }
        });
        
        // Event handler untuk form dengan class .delete-form
        $(document).off('submit', '.delete-form').on('submit', '.delete-form', function(e) {
            const form = $(this);
            if (isLocked(form)) {
                e.preventDefault();
                showLockWarning(form.data('lockMessage'));
                return;
            }

            e.preventDefault();
            const url = form.attr('action');
            const message = form.data('message') || form.find('button[type="submit"]').data('message');
            
            if (url) {
                showDeleteConfirm(url, message);
            }
        });
        
        // Handler untuk submit form delete di modal
        // Gunakan .off() untuk mencegah duplicate handlers
        $('#deleteConfirmForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const submitBtn = form.find('button[type="submit"]');
            
            // Disable button dan tampilkan loading
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...');
            
            // Submit form via AJAX
            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    $('#deleteConfirmModal').modal('hide');

                    const successMessage = response?.message || 'Data berhasil dihapus';

                    // Kembalikan tombol submit ke kondisi awal
                    submitBtn.prop('disabled', false);
                    submitBtn.html('<i class="fas fa-trash mr-2"></i>Ya, Hapus');

                    // Selaraskan perilaku dengan tambah/edit: cukup redirect dan biarkan
                    // page-transition + notifikasi berjalan otomatis di halaman tujuan
                    const targetUrl = response?.redirect
                        ? new URL(response.redirect, window.location.origin)
                        : new URL(window.location.href);

                    targetUrl.searchParams.set('from', 'crud');
                    targetUrl.searchParams.set('crudMessage', successMessage);

                    // Sedikit delay agar modal sempat tertutup rapi sebelum redirect
                    setTimeout(() => {
                        window.location.href = targetUrl.toString();
                    }, 250);
                },
                error: function(xhr, textStatus, errorThrown) {
                    // Reset button dan tutup modal
                    $('#deleteConfirmModal').modal('hide');
                    submitBtn.prop('disabled', false);
                    submitBtn.html('<i class="fas fa-trash mr-2"></i>Ya, Hapus');
                    
                    // Jika status 200 atau redirect terjadi, anggap sukses
                    // Ini bisa terjadi jika response bukan JSON valid
                    if (xhr.status === 200 || xhr.status === 302 || xhr.status === 0) {
                        // Kemungkinan besar sukses tapi response bukan JSON
                        // Lakukan reload untuk melihat perubahan
                        setTimeout(function() {
                            window.location.reload();
                        }, 250);
                        return;
                    }
                    
                    // Parse error message jika ada
                    let errorMessage = 'Gagal menghapus data. Silakan coba lagi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // Redirect dengan error message untuk notifikasi toast
                    if (xhr.responseJSON && xhr.responseJSON.redirect) {
                        const errorUrl = new URL(xhr.responseJSON.redirect, window.location.origin);
                        errorUrl.searchParams.set('from', 'crud');
                        errorUrl.searchParams.set('errorMessage', errorMessage);
                        window.location.href = errorUrl.toString();
                    } else {
                        // Hanya tampilkan alert jika benar-benar error (4xx atau 5xx)
                        if (xhr.status >= 400) {
                            alert(errorMessage);
                        } else {
                            // Fallback: reload halaman
                            window.location.reload();
                        }
                    }
                }
            });
        });
    });
    
    // Fallback jika jQuery belum tersedia saat script dijalankan
    if (typeof $ === 'undefined') {
        var checkJQuery = setInterval(function() {
            if (typeof $ !== 'undefined') {
                clearInterval(checkJQuery);
                $(document).ready(function() {
                    // Handler akan di-register di atas
                });
            }
        }, 50);
        
        setTimeout(function() {
            clearInterval(checkJQuery);
        }, 5000);
    }
})(jQuery || window.jQuery || $);

// Tidak diperlukan lagi function notifikasi khusus delete karena
// seluruh notifikasi CRUD sekarang ditangani terpusat melalui
// components/notifications.blade.php sesudah page transition selesai.
</script>
