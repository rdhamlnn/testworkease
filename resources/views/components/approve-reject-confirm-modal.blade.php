<!-- Modal Konfirmasi Approve/Reject -->
<div class="modal fade" id="approveRejectConfirmModal" tabindex="-1" role="dialog" aria-labelledby="approveRejectConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="approveRejectModalHeader">
                <h5 class="modal-title" id="approveRejectConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-question-circle" id="approveRejectIcon" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="text-center mb-3" id="approveRejectConfirmMessage">
                    Apakah Anda yakin?
                </h5>
                <p class="text-center text-muted mb-0" id="approveRejectConfirmDescription">
                    Tindakan ini akan memproses work order.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="button" class="btn" id="approveRejectConfirmBtn" onclick="handleApproveRejectConfirm()">
                    <i class="fas mr-2" id="approveRejectConfirmIcon"></i><span id="approveRejectConfirmText">Konfirmasi</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk menampilkan modal konfirmasi approve/reject
function showApproveRejectConfirm(url, type, message) {
    const modal = $('#approveRejectConfirmModal');
    const messageEl = $('#approveRejectConfirmMessage');
    const descriptionEl = $('#approveRejectConfirmDescription');
    const iconEl = $('#approveRejectIcon');
    const headerEl = $('#approveRejectModalHeader');
    const btnEl = $('#approveRejectConfirmBtn');
    const btnIconEl = $('#approveRejectConfirmIcon');
    const btnTextEl = $('#approveRejectConfirmText');
    
    // Cari form asli untuk menyimpan reference
    let originalForm = $('.approve-form[action="' + url + '"]').first();
    if (originalForm.length === 0) {
        originalForm = $('.reject-form[action="' + url + '"]').first();
    }
    
    // Simpan reference form asli di data attribute modal
    modal.data('originalForm', originalForm);
    modal.data('originalUrl', url);
    
    // Set pesan konfirmasi
    if (message) {
        messageEl.text(message);
    } else {
        messageEl.text(type === 'approve' ? 'Yakin ingin menyetujui work order ini?' : 'Yakin ingin menolak work order ini?');
    }
    
    // Set style berdasarkan type
    if (type === 'approve') {
        // Approve - Hijau
        headerEl.css('background-color', '#28a745');
        iconEl.removeClass().addClass('fas fa-check-circle text-success');
        descriptionEl.text('Work order akan disetujui dan diproses lebih lanjut.');
        btnEl.removeClass('btn-danger btn-warning').addClass('btn-success');
        btnIconEl.removeClass('fa-times fa-exclamation-triangle').addClass('fa-check');
        btnTextEl.text('Ya, Setujui');
    } else {
        // Reject - Merah
        headerEl.css('background-color', '#dc3545');
        iconEl.removeClass().addClass('fas fa-times-circle text-danger');
        descriptionEl.text('Work order akan ditolak dan tidak dapat diproses lebih lanjut.');
        btnEl.removeClass('btn-success btn-warning').addClass('btn-danger');
        btnIconEl.removeClass('fa-check fa-exclamation-triangle').addClass('fa-times');
        btnTextEl.text('Ya, Tolak');
    }
    
    // PASTIKAN BUTTON TIDAK DISABLED DAN BISA DIKLIK
    btnEl.prop('disabled', false);
    btnEl.removeAttr('disabled');
    btnEl.css({
        'pointer-events': 'auto',
        'cursor': 'pointer',
        'opacity': '1'
    });
    
    // Pastikan onclick handler terpasang
    btnEl.attr('onclick', 'handleApproveRejectConfirm()');
    
    // Tampilkan modal
    modal.modal('show');
}

// Handler untuk klik button konfirmasi - menggunakan onclick langsung
function handleApproveRejectConfirm() {
    const modal = $('#approveRejectConfirmModal');
    const url = modal.data('originalUrl');
    const submitBtn = $('#approveRejectConfirmBtn');
    const originalHtml = submitBtn.html();
    
    if (!url) {
        alert('URL tidak ditemukan. Silakan coba lagi.');
        return false;
    }
    
    // Ambil form asli dari data attribute
    let originalForm = modal.data('originalForm');
    if (!originalForm || originalForm.length === 0) {
        // Fallback: cari form asli
        originalForm = $('.approve-form[action="' + url + '"]').first();
        if (originalForm.length === 0) {
            originalForm = $('.reject-form[action="' + url + '"]').first();
        }
    }
    
    // Cari row
    let row = null;
    if (originalForm && originalForm.length > 0) {
        row = originalForm.closest('tr');
    }
    
    const isApprove = url.includes('approve');
    
    // TUTUP MODAL DULU, LALU TAMPILKAN ANIMASI
    modal.modal('hide');
    
    // Tampilkan animasi "Memproses data..." setelah modal benar-benar ditutup
    modal.one('hidden.bs.modal', function() {
        // Event ini dipanggil setelah modal benar-benar tertutup (one-time)
        if (typeof showPageTransition === 'function') {
            showPageTransition('Memproses data...');
        }
    });
    
    // Disable button dan tampilkan loading
    submitBtn.prop('disabled', true);
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
    
    // Disable buttons di row jika ditemukan
    if (row && row.length > 0) {
        row.find('.approve-btn, .reject-btn').prop('disabled', true).css({
            'background-color': '#6c757d',
            'border-color': '#6c757d',
            'cursor': 'not-allowed',
            'opacity': '0.6'
        });
    }
    
    // Submit form via AJAX dengan header yang benar
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
                // Session message sudah di-set di controller, jadi akan otomatis muncul notifikasi toast
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
                    row.find('.approve-btn, .reject-btn').prop('disabled', false).css({});
                }
                submitBtn.prop('disabled', false);
                submitBtn.html(originalHtml);
                
                // Redirect dengan from=crud untuk trigger notifikasi toast error
                // Session error sudah di-set di controller jika ada
                if (response.redirect) {
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
                row.find('.approve-btn, .reject-btn').prop('disabled', false).css({});
            }
            submitBtn.prop('disabled', false);
            submitBtn.html(originalHtml);
            
            // Redirect dengan from=crud untuk trigger notifikasi toast error
            // Session error sudah di-set di controller jika ada
            let redirectUrl = null;
            if (xhr.responseJSON && xhr.responseJSON.redirect) {
                redirectUrl = xhr.responseJSON.redirect;
            } else {
                const errorUrl = new URL(window.location.href);
                errorUrl.searchParams.set('from', 'crud');
                redirectUrl = errorUrl.toString();
            }
            
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                // Fallback: reload halaman
                location.reload();
            }
        }
    });
    
    return false;
}
</script>

<style>
#approveRejectConfirmModal .modal-dialog {
    max-width: 420px;
}

#approveRejectConfirmModal .modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
}

#approveRejectConfirmModal .modal-header {
    color: #fff;
    padding: 20px 25px;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
}

#approveRejectConfirmModal .modal-header .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

#approveRejectConfirmModal .modal-header .close {
    color: #fff;
    opacity: 1;
    font-size: 1.4rem;
    transition: opacity 0.2s ease;
}

#approveRejectConfirmModal .modal-header .close:hover {
    opacity: 0.8;
}

#approveRejectConfirmModal .modal-body {
    padding: 35px 25px;
    text-align: center;
}

#approveRejectConfirmModal .modal-body i {
    margin-bottom: 20px;
    opacity: 0.9;
}

#approveRejectConfirmModal .modal-body h5 {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    font-size: 1.15rem;
}

#approveRejectConfirmModal .modal-body p {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.5;
}

#approveRejectConfirmModal .modal-footer {
    padding: 18px 25px;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    border-radius: 0 0 10px 10px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

#approveRejectConfirmModal .modal-footer .btn {
    padding: 8px 20px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

#approveRejectConfirmModal .modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

#approveRejectConfirmModal .modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

#approveRejectConfirmModal .modal-footer .btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

#approveRejectConfirmModal .modal-footer .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

#approveRejectConfirmModal .modal-footer .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

#approveRejectConfirmModal .modal-footer .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

#approveRejectConfirmModal .modal-footer .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
