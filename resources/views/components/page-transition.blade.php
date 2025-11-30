{{-- Page Transition Animation Component --}}
@php
    $fromParam = request()->get('from');
    $shouldShow = in_array($fromParam, ['login', 'logout', 'crud']);
    $initialDisplay = $shouldShow ? 'flex' : 'none';
    $initialOpacity = $shouldShow ? '1' : '0';
@endphp

{{-- Push style to head to hide body immediately if transition needed --}}
@if($shouldShow)
@push('styles')
<style>
    /* Hide body content immediately to prevent flash - applied in head */
    body {
        overflow: hidden !important;
        visibility: hidden !important;
    }
</style>
@endpush

<script>
(function() {
    // Ensure body is hidden immediately (fallback if push doesn't work)
    if (document.body) {
        document.body.style.overflow = 'hidden';
        document.body.style.visibility = 'hidden';
    }
    // Show body when overlay is ready
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('page-transition-overlay');
        if (overlay && (overlay.style.display === 'flex' || getComputedStyle(overlay).display === 'flex')) {
            document.body.style.visibility = 'visible';
        }
    });
})();
</script>
@endif

<div id="page-transition-overlay" style="display: {{ $initialDisplay }}; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.95); z-index: 99999; justify-content: center; align-items: center; flex-direction: column; opacity: {{ $initialOpacity }}; transition: opacity 0.2s ease-in; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);">
    <div id="transition-logo" style="text-align: center; animation: logoPulse 1.5s ease-in-out infinite;">
        <img src="{{ asset('assets/img/KCE-removebg.png') }}" alt="KCE Logo" style="max-width: 200px; height: auto; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
    </div>
    <div id="transition-text" style="margin-top: 20px; font-family: 'Inter', sans-serif; font-size: 18px; font-weight: 600; color: #1B3C88; text-align: center;">
        <div id="loading-text">Memuat halaman...</div>
        <div id="loading-dots" style="margin-top: 10px;">
            <span class="dot" style="animation: dot1 1.4s infinite;">.</span>
            <span class="dot" style="animation: dot2 1.4s infinite;">.</span>
            <span class="dot" style="animation: dot3 1.4s infinite;">.</span>
        </div>
        <div id="progress-bar" style="width: 200px; height: 3px; background: rgba(27, 60, 136, 0.2); border-radius: 2px; margin: 15px auto 0; overflow: hidden;">
            <div id="progress-fill" style="height: 100%; background: linear-gradient(90deg, #1B3C88, #4A90E2); border-radius: 2px; width: 0%; transition: width 0.3s ease;"></div>
        </div>
    </div>
</div>

<style>
@keyframes logoPulse {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.05); opacity: 1; }
    100% { transform: scale(1); opacity: 0.8; }
}

@keyframes dot1 {
    0%, 20% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
}

@keyframes dot2 {
    0%, 20% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
    animation-delay: 0.2s;
}

@keyframes dot3 {
    0%, 20% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
    animation-delay: 0.4s;
}

.dot {
    display: inline-block;
    font-size: 24px;
    color: #1B3C88;
    margin: 0 2px;
}

#page-transition-overlay {
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    will-change: opacity;
    pointer-events: auto;
}

#transition-logo img {
    transition: all 0.3s ease;
}

#transition-logo:hover img {
    transform: scale(1.1);
}
</style>

<script>
// Page Transition Functions
let isTransitionShowing = false;
let isTransitionHiding = false;

function showPageTransition(message = 'Memuat halaman...') {
    // Prevent double animation
    if (isTransitionShowing) {
        console.log('showPageTransition already in progress, skipping duplicate call');
        return;
    }
    
    console.log('showPageTransition called with message:', message);
    const overlay = document.getElementById('page-transition-overlay');
    const loadingText = document.getElementById('loading-text');
    const progressFill = document.getElementById('progress-fill');
    
    if (overlay && loadingText) {
        isTransitionShowing = true;
        isTransitionHiding = false; // Reset hiding flag
        
        console.log('Overlay found, showing transition');
        loadingText.textContent = message;
        
        // Reset progress bar
        if (progressFill) {
            progressFill.style.width = '0%';
        }
        
        // Check if overlay is already visible (from inline script)
        const isAlreadyVisible = overlay.style.display === 'flex' && parseFloat(overlay.style.opacity || '0') > 0.5;
        
        if (isAlreadyVisible) {
            // Overlay already visible, just update message and start progress
            console.log('Overlay already visible, updating message only');
        } else {
            // Set overlay properties immediately to prevent gap
            overlay.style.zIndex = '99999';
            overlay.style.display = 'flex';
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.2s ease-in';
            document.body.style.overflow = 'hidden';
            
            // Force reflow to ensure display:flex is applied
            void overlay.offsetHeight;
            
            // Fade in smoothly using requestAnimationFrame to prevent gap
            requestAnimationFrame(() => {
                overlay.style.opacity = '1';
            });
        }
        
        // Animate progress bar
        if (progressFill) {
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 20 + Math.random() * 10;
                if (progress > 100) progress = 100;
                progressFill.style.width = progress + '%';
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                }
            }, 90);
        }
        
        // Reset showing flag after animation completes
        setTimeout(() => {
            isTransitionShowing = false;
        }, 250);
    } else {
        console.error('Page transition overlay or loading text not found!', {
            overlay: !!overlay,
            loadingText: !!loadingText
        });
    }
}

function hidePageTransition() {
    // Prevent double animation
    if (isTransitionHiding) {
        console.log('hidePageTransition already in progress, skipping duplicate call');
        return;
    }
    
    console.log('hidePageTransition called');
    const overlay = document.getElementById('page-transition-overlay');
    
    if (overlay) {
        isTransitionHiding = true;
        isTransitionShowing = false; // Reset showing flag
        
        console.log('Hiding overlay');
        overlay.style.transition = 'opacity 0.2s ease-out';
        overlay.style.opacity = '0';
        
        setTimeout(() => {
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
            isTransitionHiding = false;
            console.log('Overlay hidden');
            window.dispatchEvent(new CustomEvent('pageTransition:finished'));
            // Logika showCrudNotifications() sudah dihandle di notifications.blade.php
        }, 250);
    } else {
        console.warn('Overlay not found when trying to hide');
        isTransitionHiding = false;
    }
}

// Auto-hide transition after 3 seconds
function autoHideTransition() {
    setTimeout(() => {
        hidePageTransition();
    }, 3000);
}

// Enhanced transition with different messages based on user role
function showRoleBasedTransition() {
    if (window.__pageTransitionHandled) {
        console.log('Page transition already handled, skipping duplicate run.');
        return;
    }
    window.__pageTransitionHandled = true;
    
    const urlParams = new URLSearchParams(window.location.search);
    const fromLogin = urlParams.get('from') === 'login';
    const fromLogout = urlParams.get('from') === 'logout';
    const fromCrud = urlParams.get('from') === 'crud';
    
    console.log('showRoleBasedTransition called, fromLogin:', fromLogin, 'fromLogout:', fromLogout, 'fromCrud:', fromCrud, 'URL:', window.location.href);
    
    function queueNotificationAfterTransition(type) {
        const handler = () => {
            window.removeEventListener('pageTransition:finished', handler);
            window.dispatchEvent(new CustomEvent('notification:trigger', { detail: { type } }));
        };
        window.addEventListener('pageTransition:finished', handler, { once: true });
    }

    if (fromLogin) {
        // Ambil data dari session yang sudah disimpan saat login
        const jabatan = @json(session('jabatan', ''));
        const peranId = @json(session('user_peran', null));
        const currentPath = window.location.pathname.toLowerCase();
        
        console.log('Login detected! jabatan:', jabatan, 'peranId:', peranId, 'path:', currentPath);
        
        // Tentukan nama akun berdasarkan session jabatan atau fallback ke URL path
        let accountName = '';
        
        // Prioritas 1: Gunakan jabatan dari session jika tersedia
        if (jabatan && jabatan.trim() !== '') {
            accountName = jabatan.trim();
        } else {
            // Prioritas 2: Fallback ke URL path untuk menentukan akun
            if (currentPath.includes('/admin/')) {
                accountName = 'Admin';
            } else if (currentPath.includes('/logistik/')) {
                accountName = 'Logistik';
            } else if (currentPath.includes('/purchasing/')) {
                accountName = 'Purchasing';
            } else if (currentPath.includes('/kadivproduksi/')) {
                accountName = 'Kadiv Produksi';
            } else if (currentPath.includes('/kadivplasma/')) {
                accountName = 'Kadiv Plasma';
            } else if (currentPath.includes('/kadivqc/')) {
                accountName = 'Kadiv Quality Control';
            } else if (currentPath.includes('/kadivmekanik/')) {
                accountName = 'Kadiv Mekanik';
            } else if (currentPath.includes('/mekanik/')) {
                accountName = 'Mekanik';
            } else if (currentPath.includes('/atasan/')) {
                accountName = 'Atasan';
            } else {
                // Prioritas 3: Fallback berdasarkan peran ID
                if (peranId === 1) {
                    accountName = 'Admin';
                } else if (peranId === 3) {
                    accountName = 'Mekanik';
                } else if (peranId === 4 || peranId === 7) {
                    accountName = 'Atasan';
                } else {
                    accountName = 'Pengguna';
                }
            }
        }
        
        // Format pesan sesuai dengan akun yang login
        const message = `Login berhasil! Selamat datang ${accountName}. Mengakses Halaman Dashboard...`;
        
        console.log('Showing page transition with message:', message);
        showPageTransition(message);
        
        // Hide transition setelah ~1.2s agar terasa lebih cepat
        setTimeout(() => {
            console.log('Hiding page transition');
            queueNotificationAfterTransition('login');
            hidePageTransition();
        }, 1200);
        
        // Clean URL after transition
        setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.delete('from');
            url.searchParams.delete('crudMessage');
            window.history.replaceState({}, document.title, url);
        }, 1800);
    } else if (fromLogout) {
        // Handle logout notification
        console.log('Logout detected, showing transition...');
        showPageTransition('Keluar dari sistem...');
        
        // Hide transition setelah 1.5s
        setTimeout(() => {
            console.log('Hiding page transition for logout');
            queueNotificationAfterTransition('logout');
            hidePageTransition();
        }, 1500);
        
        // Clean URL after transition
        setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.delete('from');
            url.searchParams.delete('crudMessage');
            window.history.replaceState({}, document.title, url);
        }, 2000);
    } else if (fromCrud) {
        // Handle CRUD notification (tambah/edit data)
        console.log('CRUD detected, showing transition...');
        showPageTransition('Memproses data...');
        
        // Hide transition setelah 1.5s
        setTimeout(() => {
            console.log('Hiding page transition for CRUD');
            queueNotificationAfterTransition('crud');
            hidePageTransition();
        }, 1500);
        
        // Clean URL after transition
        setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.delete('from');
            url.searchParams.delete('crudMessage');
            window.history.replaceState({}, document.title, url);
        }, 2000);
    } else {
        console.log('Not from login/logout/crud, skipping transition');
    }
}

// Initialize overlay state on page load
(function() {
    const overlay = document.getElementById('page-transition-overlay');
    if (overlay) {
        const urlParams = new URLSearchParams(window.location.search);
        const fromParam = urlParams.get('from');
        
        // If overlay should be visible (has from parameter), ensure body is visible and overflow hidden
        if (fromParam && (fromParam === 'login' || fromParam === 'logout' || fromParam === 'crud')) {
            document.body.style.overflow = 'hidden';
            // Make body visible now that overlay is ready
            document.body.style.visibility = 'visible';
        }
    }
})();

// Show transition on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired, calling showRoleBasedTransition');
    showRoleBasedTransition();
});

// Show transition on form submissions (kecuali form login dan approve/reject)
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.tagName === 'FORM' && !form.classList.contains('no-transition')) {
        // Jangan tampilkan animasi untuk form login
        const isLoginForm = form.action && (form.action.includes('/login') || form.action.includes('login.authenticate'));
        const isLoginFormByClass = form.classList.contains('no-transition');
        // Jangan tampilkan animasi untuk form approve/reject (akan ditangani di modal setelah konfirmasi)
        const isApproveRejectForm = form.classList.contains('approve-form') || form.classList.contains('reject-form');
        
        console.log('Form submitted:', form.action, 'isLoginForm:', isLoginForm, 'isLoginFormByClass:', isLoginFormByClass, 'isApproveRejectForm:', isApproveRejectForm); // Debug log
        
        if (!isLoginForm && !isLoginFormByClass && !isApproveRejectForm) {
            // Hapus showPageTransition di sini karena animasi akan muncul setelah redirect di showRoleBasedTransition()
            // showPageTransition('Memproses data...');
            
            // Simpan flag bahwa form sedang di-submit untuk CRUD
            // Parameter from=crud akan ditambahkan di controller saat redirect
            // Animasi akan muncul setelah redirect di showRoleBasedTransition()
        }
    }
});

// Show transition on logout - menggunakan event delegation yang lebih reliable
document.addEventListener('click', function(e) {
    // Cek apakah yang diklik adalah link logout atau elemen di dalamnya
    let logoutLink = null;
    
    if (e.target.matches('a[href*="/logout"]')) {
        logoutLink = e.target;
    } else if (e.target.closest('a[href*="/logout"]')) {
        logoutLink = e.target.closest('a[href*="/logout"]');
    }
    
    if (logoutLink) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        console.log('Logout clicked, redirecting...'); // Debug log
        
        // Hapus showPageTransition di sini karena animasi akan muncul setelah redirect di showRoleBasedTransition()
        // showPageTransition('Keluar dari sistem...');
        
        // Redirect langsung dengan parameter from=logout
        // Animasi akan muncul setelah redirect di showRoleBasedTransition()
        const logoutUrl = new URL(logoutLink.href, window.location.origin);
        logoutUrl.searchParams.set('from', 'logout');
        window.location.href = logoutUrl.toString();
    }
});
</script>
