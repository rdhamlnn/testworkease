<!-- Modal Konfirmasi Umum -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="confirmModalHeader">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-question-circle" id="confirmIcon" style="font-size: 3.5rem; color: #1B3C88;"></i>
                </div>
                <h5 class="text-center mb-3" id="confirmMessage">
                    Apakah Anda yakin?
                </h5>
                <p class="text-center text-muted mb-0" id="confirmDescription">
                    Tindakan ini akan memproses data.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn" id="confirmBtn" onclick="handleConfirmAction()">
                    <span id="confirmText">Konfirmasi</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Pastikan jQuery sudah dimuat sebelum menggunakan
(function() {
    // Tunggu jQuery tersedia
    function initConfirmModal() {
        if (typeof jQuery === 'undefined') {
            console.error('[Confirm Modal] jQuery is not loaded!');
            return;
        }
        
        // Fungsi untuk menampilkan modal konfirmasi umum
        window.showConfirmModal = function(url, message, description, buttonText, buttonClass, iconClass) {
            if (typeof jQuery === 'undefined') {
                console.error('[Confirm Modal] jQuery is not available!');
                alert(message || 'Yakin ingin melanjutkan?');
                return;
            }
            
            const modal = $('#confirmModal');
            const messageEl = $('#confirmMessage');
            const descriptionEl = $('#confirmDescription');
            const iconEl = $('#confirmIcon');
            const headerEl = $('#confirmModalHeader');
            const btnEl = $('#confirmBtn');
            const btnTextEl = $('#confirmText');
            
            // Cari form asli untuk menyimpan reference
            let originalForm = $('.confirm-form[action="' + url + '"]').first();
            
            // Simpan reference form asli di data attribute modal
            modal.data('originalForm', originalForm);
            modal.data('originalUrl', url);
            
            // Set pesan konfirmasi
            if (message) {
                messageEl.text(message);
            } else {
                messageEl.text('Apakah Anda yakin?');
            }
            
            // Set deskripsi
            if (description) {
                descriptionEl.text(description);
            } else {
                descriptionEl.text('Tindakan ini akan memproses data.');
            }
            
            // Set icon
            if (iconClass) {
                iconEl.removeClass().addClass(iconClass);
            } else {
                iconEl.removeClass().addClass('fas fa-question-circle');
                iconEl.css('color', '#1B3C88');
            }
            
            // Set style header dan button
            const btnClass = buttonClass || 'btn-primary';
            headerEl.css('background-color', btnClass === 'btn-success' ? '#28a745' : btnClass === 'btn-danger' ? '#dc3545' : '#1B3C88');
            btnEl.removeClass('btn-success btn-danger btn-primary btn-warning').addClass(btnClass);
            
            // Set text button (tanpa ikon)
            btnTextEl.text(buttonText || 'Konfirmasi');
            
            // PASTIKAN BUTTON TIDAK DISABLED DAN BISA DIKLIK
            btnEl.prop('disabled', false);
            btnEl.removeAttr('disabled');
            btnEl.css({
                'pointer-events': 'auto',
                'cursor': 'pointer',
                'opacity': '1'
            });
            
            // Pastikan onclick handler terpasang
            btnEl.attr('onclick', 'handleConfirmAction()');
            
            // Tampilkan modal
            modal.modal('show');
        };
        
        // Handler untuk klik button konfirmasi
        window.handleConfirmAction = function() {
            if (typeof jQuery === 'undefined') {
                console.error('[Confirm Modal] jQuery is not available!');
                return false;
            }
            
            const modal = $('#confirmModal');
    const url = modal.data('originalUrl');
    const submitBtn = $('#confirmBtn');
    const originalHtml = submitBtn.html();
    
    if (!url) {
        alert('URL tidak ditemukan. Silakan coba lagi.');
        return false;
    }
    
    // Ambil form asli dari data attribute
    let originalForm = modal.data('originalForm');
    if (!originalForm || originalForm.length === 0) {
        // Fallback: cari form asli
        originalForm = $('.confirm-form[action="' + url + '"]').first();
    }
    
    // Cari row
    let row = null;
    if (originalForm && originalForm.length > 0) {
        row = originalForm.closest('tr');
    }
    
    // TUTUP MODAL DULU, LALU TAMPILKAN ANIMASI
    modal.modal('hide');
    
    // Tampilkan animasi "Memproses data..." setelah modal benar-benar ditutup
    modal.one('hidden.bs.modal', function() {
        if (typeof showPageTransition === 'function') {
            showPageTransition('Memproses data...');
        }
    });
    
    // Disable button dan tampilkan loading
    submitBtn.prop('disabled', true);
    submitBtn.html('Memproses...');
    
    // Disable buttons di row jika ditemukan
    if (row && row.length > 0) {
        row.find('button[type="submit"]').prop('disabled', true).css({
            'background-color': '#6c757d',
            'border-color': '#6c757d',
            'cursor': 'not-allowed',
            'opacity': '0.6'
        });
    }
    
    // Submit form via AJAX
    $.ajax({
        url: url,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: originalForm && originalForm.length > 0 ? originalForm.serialize() : '_token=' + ($('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val() || ''),
        dataType: 'json',
        success: function(response) {
            if (response && response.success) {
                // Sembunyikan animasi sebelum redirect
                if (typeof hidePageTransition === 'function') {
                    hidePageTransition();
                }
                
                // Redirect dengan URL yang sudah include from=crud untuk trigger notifikasi toast
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    // Fallback: redirect dengan from=crud
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('from', 'crud');
                    window.location.href = currentUrl.toString();
                }
            } else {
                // Sembunyikan animasi jika error
                if (typeof hidePageTransition === 'function') {
                    hidePageTransition();
                }
                // Re-enable buttons on error
                if (row && row.length > 0) {
                    row.find('button[type="submit"]').prop('disabled', false).css({});
                }
                submitBtn.prop('disabled', false);
                submitBtn.html('<span id="confirmText">' + (buttonText || 'Konfirmasi') + '</span>');
                
                // Redirect dengan from=crud untuk trigger notifikasi toast error
                if (response && response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    const errorUrl = new URL(window.location.href);
                    errorUrl.searchParams.set('from', 'crud');
                    window.location.href = errorUrl.toString();
                }
            }
        },
        error: function(xhr) {
            // Sembunyikan animasi jika error
            if (typeof hidePageTransition === 'function') {
                hidePageTransition();
            }
            // Re-enable buttons on error
            if (row && row.length > 0) {
                row.find('button[type="submit"]').prop('disabled', false).css({});
            }
            submitBtn.prop('disabled', false);
            const buttonText = $('#confirmText').text() || 'Konfirmasi';
            submitBtn.html('<span id="confirmText">' + buttonText + '</span>');
            
            let errorMessage = 'Terjadi kesalahan saat memproses data.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 0) {
                errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
            } else if (xhr.status === 500) {
                errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
            } else if (xhr.status === 404) {
                errorMessage = 'Halaman tidak ditemukan.';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki izin untuk melakukan tindakan ini.';
            } else if (xhr.status === 405) {
                errorMessage = 'Method tidak didukung. Silakan refresh halaman dan coba lagi.';
            }
            
            // Tampilkan error toast langsung
            if (typeof triggerToast === 'function') {
                triggerToast(errorMessage, 'danger');
            }
            
            // Redirect dengan from=crud untuk trigger notifikasi toast error (fallback)
            const errorUrl = new URL(window.location.href);
            errorUrl.searchParams.set('from', 'crud');
            errorUrl.searchParams.set('errorMessage', encodeURIComponent(errorMessage));
            // Delay redirect sedikit untuk toast muncul dulu
            setTimeout(() => {
                window.location.href = errorUrl.toString();
            }, 1000);
        }
    });
    
            return false;
        };
    }
    
    // Inisialisasi saat DOM ready atau jQuery tersedia
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu jQuery dimuat
            if (typeof jQuery !== 'undefined') {
                initConfirmModal();
            } else {
                // Polling untuk menunggu jQuery
                const checkJQuery = setInterval(function() {
                    if (typeof jQuery !== 'undefined') {
                        clearInterval(checkJQuery);
                        initConfirmModal();
                    }
                }, 100);
                // Timeout setelah 5 detik
                setTimeout(function() {
                    clearInterval(checkJQuery);
                    if (typeof jQuery === 'undefined') {
                        console.error('[Confirm Modal] jQuery tidak dimuat setelah 5 detik!');
                    }
                }, 5000);
            }
        });
    } else {
        // DOM sudah ready, langsung init jika jQuery tersedia
        if (typeof jQuery !== 'undefined') {
            initConfirmModal();
        } else {
            // Tunggu jQuery
            const checkJQuery = setInterval(function() {
                if (typeof jQuery !== 'undefined') {
                    clearInterval(checkJQuery);
                    initConfirmModal();
                }
            }, 100);
            setTimeout(function() {
                clearInterval(checkJQuery);
            }, 5000);
        }
    }
})();
</script>
@endpush

<style>
#confirmModal .modal-dialog {
    max-width: 420px;
}

#confirmModal .modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
}

#confirmModal .modal-header {
    color: #fff;
    padding: 20px 25px;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
}

#confirmModal .modal-header .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

#confirmModal .modal-header .close {
    color: #fff;
    opacity: 1;
    font-size: 1.4rem;
    transition: opacity 0.2s ease;
}

#confirmModal .modal-header .close:hover {
    opacity: 0.8;
}

#confirmModal .modal-body {
    padding: 35px 25px;
    text-align: center;
}

#confirmModal .modal-body i {
    margin-bottom: 20px;
    opacity: 0.9;
}

#confirmModal .modal-body h5 {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    font-size: 1.15rem;
}

#confirmModal .modal-body p {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.5;
}

#confirmModal .modal-footer {
    padding: 18px 25px;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    border-radius: 0 0 10px 10px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

#confirmModal .modal-footer .btn {
    padding: 8px 20px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

#confirmModal .modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

#confirmModal .modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

#confirmModal .modal-footer .btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

#confirmModal .modal-footer .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

#confirmModal .modal-footer .btn-primary {
    background-color: #1B3C88;
    border-color: #1B3C88;
}

#confirmModal .modal-footer .btn-primary:hover {
    background-color: #16316F;
    border-color: #16316F;
}

#confirmModal .modal-footer .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

