    @php
    $normalize = fn ($text) => trim(preg_replace('/\s+/', ' ', strip_tags($text ?? '')));
    
        $sessionSuccess = session('success');
    $sessionError = session('error');
    $sessionWarning = session('warning');
    $sessionInfo = session('info');
    $fromParam = request()->query('from');
    $errorMessageParam = request()->query('errorMessage');
    
    // Deteksi login vs CRUD success
    $isLogin = $sessionSuccess && (
        str_contains($sessionSuccess, 'Login berhasil') ||
        str_contains($sessionSuccess, 'Login sukses') ||
        str_contains($sessionSuccess, 'Selamat datang')
    );
    
    // Deteksi logout vs error CRUD
    $isLogout = false;
    if ($fromParam === 'logout') {
        // Jika ada parameter from=logout, langsung anggap logout
        $isLogout = true;
    } elseif ($sessionError) {
        $errorLower = strtolower($sessionError);
        $isLogout = str_contains($errorLower, 'logout berhasil') ||
                    str_contains($errorLower, 'terima kasih telah menggunakan') ||
                    str_contains($errorLower, 'keluar') ||
                    str_contains($errorLower, 'logout');
    }
    
    $notifications = collect([]);
    
    // Login notification
    if ($isLogin || $fromParam === 'login') {
        $message = $normalize($sessionSuccess) ?: 'Selamat datang kembali, akses Anda sudah aktif.';
        $notifications->push([
            'variant' => 'success',
            'message' => $message,
            'timeout' => 3500,
        ]);
    }
    // CRUD success notification
    elseif ($sessionSuccess || $fromParam === 'crud') {
        $rawMessage = $normalize($sessionSuccess);
        
        // Deteksi dan ubah pesan delete menjadi lebih spesifik
        $isDeleteMessage = false;
        if ($rawMessage) {
            $messageLower = strtolower($rawMessage);
            $isDeleteMessage = (
                str_contains($messageLower, 'berhasil dihapus') ||
                str_contains($messageLower, 'data dihapus') ||
                str_contains($messageLower, 'hapus berhasil') ||
                str_contains($messageLower, 'dihapus') ||
                (str_contains($messageLower, 'hapus') && str_contains($messageLower, 'berhasil'))
            );
        }
        
        if ($isDeleteMessage) {
            $message = 'Data berhasil dihapus dari sistem.';
        } else {
            $message = $rawMessage ?: 'Data berhasil dihapus.';
        }
        
        $notifications->push([
            'variant' => 'success',
            'message' => $message,
            'timeout' => 3000,
        ]);
    }
    
    // Logout notification - prioritas tinggi jika from=logout
    // HARUS di-check terpisah dan tidak boleh di-elseif dengan CRUD error
    if ($fromParam === 'logout') {
        // Jika from=logout, SELALU tampilkan notifikasi logout
        // Jangan bergantung pada session error karena mungkin sudah dihapus oleh invalidate()
        $message = $normalize($sessionError);
        if (empty($message) || trim($message) === '') {
            $message = 'Anda telah keluar dari sistem dengan aman. Sampai jumpa pada sesi berikutnya.';
        }
        $notifications->push([
            'variant' => 'danger',
            'message' => $message,
            'timeout' => 3500,
        ]);
    } elseif ($isLogout) {
        // Fallback jika terdeteksi dari pesan error (tanpa parameter from)
        $message = $normalize($sessionError);
        if (empty($message) || trim($message) === '') {
            $message = 'Anda telah keluar dari sistem dengan aman.';
        }
        $notifications->push([
            'variant' => 'danger',
            'message' => $message,
            'timeout' => 3500,
        ]);
    }
    // CRUD error notification - hanya jika bukan logout
    elseif ($sessionError && !$isLogout && $fromParam !== 'logout') {
        $message = $normalize($sessionError) ?: 'Terjadi kendala saat memproses data.';
        $notifications->push([
            'variant' => 'danger',
            'message' => $message,
            'timeout' => 3500,
        ]);
    }
    
    // Error message dari URL parameter (untuk validasi edit/hapus)
    if ($errorMessageParam && $fromParam === 'crud') {
        $notifications->push([
            'variant' => 'danger',
            'message' => $normalize($errorMessageParam),
            'timeout' => 3500,
        ]);
    }
    
    // Warning notification
    if ($sessionWarning) {
        $notifications->push([
            'variant' => 'warning',
            'message' => $normalize($sessionWarning) ?: 'Ada data yang perlu ditinjau ulang.',
            'timeout' => 3500,
        ]);
    }
    
    // Info notification
    if ($sessionInfo) {
        $notifications->push([
            'variant' => 'info',
            'message' => $normalize($sessionInfo) ?: 'Informasi terbaru dari sistem.',
            'timeout' => 3000,
        ]);
    }
    
    $shouldDelay = in_array($fromParam, ['login', 'logout', 'crud'], true);
    @endphp

<div id="toast-root"
     data-toasts='@json($notifications)'
     data-delay="{{ $shouldDelay ? 'true' : 'false' }}"></div>

@push('styles')
<style>
#toast-root {
    position: fixed;
    top: 24px;
    right: 24px;
    width: min(400px, calc(100% - 32px));
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 99999;
    pointer-events: none;
    font-family: 'Inter', 'Segoe UI', sans-serif;
}

#toast-root .toast {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 12px;
    color: #fff;
    background: #0f172a;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    pointer-events: auto;
    animation: toastFadeIn .32s ease;
    min-width: 0;
    width: 100%;
    position: relative;
    max-width: 400px;
}

#toast-root .toast[data-variant="success"] { 
    background: #059669; 
    color: #fff;
}
#toast-root .toast[data-variant="danger"]  { 
    background: #ef4444; 
    color: #fff;
}
#toast-root .toast[data-variant="warning"] { 
    background: #f59e0b; 
    color: #1f2937;
}
#toast-root .toast[data-variant="info"]    { 
    background: #3b82f6; 
    color: #fff;
}

.toast-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    position: relative;
}

.toast-icon i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 18px;
    line-height: 1;
    margin: 0;
    padding: 0;
}

.toast-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    padding-right: 8px;
}

.toast-title {
    font-weight: 700;
    font-size: 16px;
    line-height: 1.3;
    color: #fff;
    margin-bottom: 2px;
}

#toast-root .toast[data-variant="warning"] .toast-title {
    color: #1f2937;
}

.toast-message {
    font-size: 13px;
    line-height: 1.5;
    font-weight: 400;
    color: rgba(255,255,255,0.85);
    word-wrap: break-word;
}

#toast-root .toast[data-variant="warning"] .toast-message {
    color: rgba(31,41,55,0.8);
}

.toast-close {
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    color: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s;
    padding: 0;
    margin: 0;
    position: relative;
    pointer-events: auto;
    z-index: 1;
    outline: none;
    box-shadow: none;
}

.toast-close:focus,
.toast-close:active,
.toast-close:focus-visible {
    outline: none;
    border: none;
    box-shadow: none;
}

.toast-close i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    line-height: 1;
    margin: 0;
    padding: 0;
}

.toast-close:hover { 
    background: rgba(255,255,255,0.3); 
}

.toast-leave { animation: toastFadeOut .22s ease forwards; }

@keyframes toastFadeIn {
    from { opacity: 0; transform: translateX(38px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes toastFadeOut {
    to { opacity: 0; transform: translateX(38px); }
}

@media (max-width: 640px) {
    #toast-root {
        left: 16px;
        right: 16px;
        width: auto;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    console.log('[Toast] Script loading...');
    const root = document.getElementById('toast-root');
    if (!root) {
        console.error('[Toast] Root element not found!');
        return;
    }
    console.log('[Toast] Root element found');
    
    // Ensure root is in body and visible
    if (!document.body.contains(root)) {
        console.warn('[Toast] Root not in body, appending...');
        document.body.appendChild(root);
    }
    
    // Force display and ensure root has correct styles
    const rootStyle = window.getComputedStyle(root);
    if (rootStyle.display === 'none') {
        console.warn('[Toast] Root is hidden, forcing display...');
        root.style.display = 'flex';
    }
    
    // Ensure root has correct positioning
    if (!rootStyle.position || rootStyle.position === 'static') {
        root.style.position = 'fixed';
        root.style.top = '24px';
        root.style.right = '24px';
        root.style.zIndex = '99999';
        root.style.display = 'flex';
        root.style.flexDirection = 'column';
        root.style.gap = '12px';
        root.style.pointerEvents = 'none';
        root.style.width = 'min(400px, calc(100% - 32px))';
        console.log('[Toast] Applied inline styles to root');
    }

    let queue = [];
    try {
        const rawData = root.dataset.toasts || '[]';
        console.log('[Toast] Raw payload:', rawData);
        console.log('[Toast] From param:', root.dataset.delay === 'true' ? 'has delay (login/logout/crud)' : 'no delay');
        queue = JSON.parse(rawData);
        console.log('[Toast] Parsed queue:', queue);
        console.log('[Toast] Queue length:', queue.length);
        if (queue.length > 0) {
            console.log('[Toast] First notification:', queue[0]);
            queue.forEach((notif, index) => {
                console.log(`[Toast] Notification ${index + 1}:`, notif);
            });
        }
    } catch (error) {
        console.error('[Toast] Payload parse error:', error);
        queue = [];
    }
    
    if (!Array.isArray(queue)) {
        console.warn('[Toast] Queue is not an array, resetting');
        queue = [];
    }

    const iconMap = {
        success: 'fas fa-check-circle',
        danger: 'fas fa-sign-out-alt',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };

    // Event delegation untuk close button (lebih reliable)
    root.addEventListener('click', (event) => {
        const closeButton = event.target.closest('.toast-close');
        if (closeButton) {
            event.preventDefault();
            event.stopPropagation();
            const toast = closeButton.closest('.toast');
            if (toast) {
                console.log('[Toast] Close button clicked via delegation');
                if (toast.dataset.closed === 'true') {
                    return;
                }
                toast.dataset.closed = 'true';
                toast.classList.add('toast-leave');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                        console.log('[Toast] Toast removed from DOM');
                    }
                }, 230);
            }
        }
    });

    function createToast({ variant = 'info', message = '', timeout = 4200 }) {
        if (!message) {
            console.warn('[Toast] Skipping toast with empty message');
            return;
        }

        console.log('[Toast] Creating toast:', variant, message.substring(0, 50));
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.dataset.variant = variant;
        
        // Tentukan title dan icon berdasarkan variant dan message
        const messageLower = message.toLowerCase();
        let title = 'Notifikasi';
        let iconClass = iconMap[variant] || iconMap.info;
        
        if (variant === 'danger' && (messageLower.includes('logout') || messageLower.includes('keluar'))) {
            title = 'Logout Berhasil';
            iconClass = 'fas fa-sign-out-alt';
        } else if (variant === 'success' && (messageLower.includes('login') || messageLower.includes('selamat datang'))) {
            title = 'Login Berhasil';
            iconClass = 'fas fa-check-circle';
        } else if (variant === 'success' && messageLower.includes('dihapus')) {
            title = 'Data Dihapus';
            iconClass = 'fas fa-trash-alt';
        } else if (variant === 'success' && (messageLower.includes('ditambahkan') || messageLower.includes('disimpan') || messageLower.includes('diubah'))) {
            title = 'Data Tersimpan';
            iconClass = 'fas fa-check-circle';
        } else if (variant === 'success') {
            title = 'Berhasil';
            iconClass = 'fas fa-check-circle';
        } else if (variant === 'danger') {
            title = 'Error';
            iconClass = 'fas fa-times-circle';
        } else if (variant === 'warning') {
            title = 'Peringatan';
            iconClass = 'fas fa-exclamation-triangle';
        } else if (variant === 'info') {
            title = 'Informasi';
            iconClass = 'fas fa-info-circle';
        }
        
        // Set background based on variant (solid colors)
        const bgColors = {
            success: '#059669',
            danger: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        const textColors = {
            success: '#fff',
            danger: '#fff',
            warning: '#1f2937',
            info: '#fff'
        };
        
        // Ensure toast is visible with inline styles as fallback
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '14px';
        toast.style.padding = '16px 18px';
        toast.style.borderRadius = '12px';
        toast.style.color = textColors[variant] || '#fff';
        toast.style.background = bgColors[variant] || bgColors.info;
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.style.pointerEvents = 'auto';
        toast.style.position = 'relative';
        toast.style.zIndex = '99999';
        toast.style.width = '100%';
        toast.style.maxWidth = '400px';
        
        const titleColor = variant === 'warning' ? '#1f2937' : '#fff';
        const messageColor = variant === 'warning' ? 'rgba(31,41,55,0.8)' : 'rgba(255,255,255,0.85)';
        
        toast.innerHTML = `
            <div class="toast-icon" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; position: relative;">
                <i class="${iconClass}" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 18px; line-height: 1; margin: 0; padding: 0;"></i>
            </div>
            <div class="toast-content" style="flex: 1; display: flex; flex-direction: column; gap: 6px; min-width: 0; padding-right: 8px;">
                <div class="toast-title" style="font-weight: 700; font-size: 16px; line-height: 1.3; color: ${titleColor}; margin-bottom: 2px;">${title}</div>
                <div class="toast-message" style="font-size: 13px; line-height: 1.5; font-weight: 400; color: ${messageColor}; word-wrap: break-word;">${message}</div>
            </div>
            <button type="button" class="toast-close" aria-label="Tutup" style="border: none; width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.2); color: inherit; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.2s; padding: 0; margin: 0; position: relative; pointer-events: auto; z-index: 1; outline: none; box-shadow: none;">
                <i class="fas fa-times" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 12px; line-height: 1; margin: 0; padding: 0; pointer-events: none;"></i>
            </button>
        `;

        const removeToast = () => {
            if (toast.dataset.closed === 'true') {
                return;
            }
            toast.dataset.closed = 'true';
            toast.classList.add('toast-leave');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                    console.log('[Toast] Toast removed from DOM');
                }
            }, 230);
        };

        // Append to DOM (event delegation handles close button clicks)
        root.appendChild(toast);
        console.log('[Toast] Toast appended to DOM, root children:', root.children.length);
        console.log('[Toast] Toast element:', toast);
        console.log('[Toast] Toast computed style:', window.getComputedStyle(toast).display);
        
        // Set timeout after append
        setTimeout(() => {
            console.log('[Toast] Auto-hide timeout triggered');
            removeToast();
        }, timeout);
    }

    function flushQueue() {
        console.log('[Toast] Flushing queue, count:', queue.length);
        
        // Ensure root is visible
        const rootStyle = window.getComputedStyle(root);
        console.log('[Toast] Root computed style - display:', rootStyle.display, 'visibility:', rootStyle.visibility, 'opacity:', rootStyle.opacity);
        
        if (rootStyle.display === 'none') {
            console.warn('[Toast] Root is hidden! Forcing display...');
            root.style.display = 'flex';
        }
        
        queue.forEach(createToast);
        queue = [];
    }

    function maybeWaitForTransition() {
        console.log('[Toast] Initializing, queue length:', queue.length, 'delay:', root.dataset.delay);
        
        if (root.dataset.delay !== 'true') {
            console.log('[Toast] No delay needed, showing immediately');
            flushQueue();
            return;
        }

        console.log('[Toast] Waiting for page transition to finish...');
        let resolved = false;
        
        const finish = () => {
            if (resolved) {
                return;
            }
            resolved = true;
            console.log('[Toast] Transition finished, showing toasts');
            flushQueue();
        };

        // Listen for event
        const handler = () => {
            console.log('[Toast] Received pageTransition:finished event');
            window.removeEventListener('pageTransition:finished', handler);
            finish();
        };
        window.addEventListener('pageTransition:finished', handler, { once: true });

        // Polling fallback - check overlay visibility
        const overlay = document.getElementById('page-transition-overlay');
        let attempts = 0;
        const maxAttempts = 30;
        
        const poll = () => {
            if (resolved) {
                return;
            }
            attempts++;
            
            let overlayVisible = false;
            if (overlay) {
                const style = window.getComputedStyle(overlay);
                overlayVisible = style.display !== 'none' && parseFloat(style.opacity || '0') > 0.05;
            }
            
            if (!overlayVisible || attempts >= maxAttempts) {
                console.log('[Toast] Polling finished, overlay visible:', overlayVisible, 'attempts:', attempts);
                finish();
                return;
            }
            
            setTimeout(poll, 100);
        };
        
        // Start polling after a short delay
        setTimeout(poll, 100);
        
        // Ultimate fallback - show after 2.5 seconds no matter what
        setTimeout(() => {
            if (!resolved) {
                console.log('[Toast] Ultimate fallback triggered after 2.5s');
                finish();
            }
        }, 2500);
        
        // Special fallback for logout - show immediately if no overlay found
        const urlParams = new URLSearchParams(window.location.search);
        const fromParam = urlParams.get('from');
        if (fromParam === 'logout' && !overlay) {
            console.log('[Toast] Logout detected, no overlay found, showing immediately');
            setTimeout(() => {
                if (!resolved) {
                    finish();
                }
            }, 500);
        }
    }

    // Run immediately when script loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', maybeWaitForTransition);
    } else {
        maybeWaitForTransition();
    }

    window.addEventListener('toast:add', (event) => {
        createToast(event.detail || {});
    });

    window.triggerToast = function(message, variant = 'danger', timeout = 4800) {
        if (!message) {
            return;
        }
        window.dispatchEvent(new CustomEvent('toast:add', {
            detail: { variant, message, timeout }
        }));
    };
})();
</script>
@endpush

