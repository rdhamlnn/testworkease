/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 * 
 */

"use strict";

/**
 * Fix untuk masalah nicescroll yang mengganggu scrolling di dalam modal
 * Nicescroll akan di-disable pada modal-body saat modal dibuka,
 * dan native browser scrolling akan digunakan sebagai gantinya.
 */
$(document).ready(function() {
    // Saat modal dibuka, pastikan scroll berjalan dengan baik
    $(document).on('shown.bs.modal', '.modal', function() {
        var $modal = $(this);
        var $modalBody = $modal.find('.modal-body');
        var $modalContent = $modal.find('.modal-content');
        var $tabContentScrollable = $modal.find('.tab-content-scrollable');
        
        // Hapus nicescroll jika ada
        if ($modalBody.getNiceScroll && $modalBody.getNiceScroll().length > 0) {
            $modalBody.getNiceScroll().remove();
        }
        
        if ($tabContentScrollable.getNiceScroll && $tabContentScrollable.getNiceScroll().length > 0) {
            $tabContentScrollable.getNiceScroll().remove();
        }
        
        // Pastikan CSS untuk scroll benar
        $modalBody.css({
            'overflow-y': 'auto',
            'overflow-x': 'hidden',
            'max-height': 'calc(100vh - 210px)',
            '-webkit-overflow-scrolling': 'touch'
        });
        
        $tabContentScrollable.css({
            'overflow-y': 'auto',
            'overflow-x': 'hidden',
            'max-height': '450px',
            '-webkit-overflow-scrolling': 'touch'
        });
        
        // Pastikan modal-content tidak blocking scroll
        $modalContent.css('overflow', 'visible');
        
        // Force reflow untuk memastikan scrollbar muncul
        $modalBody[0] && ($modalBody[0].offsetHeight);
    });
    
    // Saat modal tertutup, bersihkan style inline
    $(document).on('hidden.bs.modal', '.modal', function() {
        var $modal = $(this);
        var $modalBody = $modal.find('.modal-body');
        
        // Reset ke default
        $modalBody.css({
            'overflow-y': '',
            'overflow-x': '',
            'max-height': ''
        });
    });
    
    // Fix untuk Select2 di dalam modal
    $(document).on('select2:open', function() {
        // Delay sedikit untuk memastikan dropdown sudah ter-render
        setTimeout(function() {
            var $dropdown = $('.select2-container--open .select2-dropdown');
            if ($dropdown.length) {
                $dropdown.css('z-index', '10055');
            }
            
            // Fokuskan search field
            var searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        }, 10);
    });
});
