<!-- jQuery -->
<script src="<?= base_url('template/backend/plugins/jquery/jquery.min.js') ?>"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?= base_url('template/backend/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('template/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

<!-- AdminLTE App -->
<script src="<?= base_url('template/backend/dist/js/adminlte.js') ?>"></script>

<!-- DataTables -->
<script src="<?= base_url('template/backend/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>

<!-- Select2 -->
<script src="<?= base_url('template/backend/plugins/select2/js/select2.full.min.js') ?>"></script>

<!-- SweetAlert2 -->
<script src="<?= base_url('template/backend/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<!-- Custom Scripts -->
<script>
// Base URL for AJAX requests
const baseUrl = '<?= rtrim(base_url(), '/') ?>';

$(document).ready(function() {
    // =================================================================
    // LOADING OVERLAY
    // =================================================================
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        setTimeout(function() {
            $(loader).fadeOut('fast');
        }, 300); 
    }
    
    // =================================================================
    // DARK MODE TOGGLE
    // =================================================================
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeIcon = document.getElementById('darkModeIcon');
    const body = document.body;
    
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        body.classList.add('dark-mode');
        if (darkModeIcon) {
            darkModeIcon.classList.remove('fa-moon');
            darkModeIcon.classList.add('fa-sun');
        }
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('dark-mode');
            
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            
            if (darkModeIcon) {
                if (isDark) {
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                } else {
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                }
            }
        });
    }
    
    // =================================================================
    // FLASH MESSAGES dengan SweetAlert2
    // =================================================================
    <?php if (session()->getFlashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        html: `<?= esc(session()->getFlashdata('success'), 'js') ?>`,
        timer: 3000,
        showConfirmButton: false
    });
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: `<?= esc(session()->getFlashdata('error'), 'js') ?>`,
        timer: 5000,
        showConfirmButton: true
    });
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('warning')): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Perhatian!',
        text: `<?= esc(session()->getFlashdata('warning'), 'js') ?>`,
        timer: 4000,
        showConfirmButton: true
    });
    <?php endif; ?>
    
    // =================================================================
    // KONFIRMASI HAPUS
    // =================================================================
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const name = $(this).data('name') || 'item ini';
        
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data ' + name + ' akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
    
    // =================================================================
    // DATATABLES DEFAULT CONFIG
    // =================================================================
    if ($.fn.DataTable) {
        $.extend($.fn.dataTable.defaults, {
            language: {
                "sEmptyTable":   "Tidak ada data yang tersedia pada tabel ini",
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ entri",
                "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sInfoPostFix":  "",
                "sSearch":       "Cari:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            responsive: true
        });
    }
    // =================================================================
    // IMAGE POPUP PREVIEW GLOBAL HANDLER (WITH ZOOM & PAN)
    // =================================================================
    let currentScale = 1;
    let isDragging = false;
    let startX, startY;
    let translateX = 0, translateY = 0;

    const imgTarget = $('#modalViewImageTarget');

    function updateImageTransform() {
        imgTarget.css('transform', `translate(${translateX}px, ${translateY}px) scale(${currentScale})`);
        if (currentScale > 1) {
            imgTarget.css('cursor', 'grab');
        } else {
            imgTarget.css('cursor', 'default');
        }
    }

    $(document).on('click', '.view-image-popup', function(e) {
        e.preventDefault();
        const imageUrl = $(this).attr('href') || $(this).data('url');
        if (imageUrl) {
            // Reset state
            currentScale = 1;
            translateX = 0;
            translateY = 0;
            imgTarget.css({
                'transform': 'none',
                'cursor': 'default'
            });
            imgTarget.attr('src', imageUrl);
            $('#btnDownloadImage').attr('href', imageUrl);
            $('#modalViewImage').modal('show');
        }
    });

    // Zoom Controls
    $(document).on('click', '#btnZoomIn', function() {
        currentScale = Math.min(4, currentScale + 0.25);
        updateImageTransform();
    });

    $(document).on('click', '#btnZoomOut', function() {
        currentScale = Math.max(0.5, currentScale - 0.25);
        if (currentScale <= 1) {
            translateX = 0;
            translateY = 0;
        }
        updateImageTransform();
    });

    $(document).on('click', '#btnZoomReset', function() {
        currentScale = 1;
        translateX = 0;
        translateY = 0;
        updateImageTransform();
    });

    // Reset zoom when modal is closed
    $('#modalViewImage').on('hidden.bs.modal', function() {
        currentScale = 1;
        translateX = 0;
        translateY = 0;
        imgTarget.css({
            'transform': 'none',
            'cursor': 'default'
        });
    });

    // Drag / Pan events
    imgTarget.on('mousedown touchstart', function(e) {
        if (currentScale <= 1) return;
        isDragging = true;
        imgTarget.css('cursor', 'grabbing');
        
        const pageX = e.pageX || (e.originalEvent.touches && e.originalEvent.touches[0].pageX);
        const pageY = e.pageY || (e.originalEvent.touches && e.originalEvent.touches[0].pageY);
        
        startX = pageX - translateX;
        startY = pageY - translateY;
        e.preventDefault();
    });

    $(document).on('mousemove touchmove', function(e) {
        if (!isDragging) return;
        
        const pageX = e.pageX || (e.originalEvent.touches && e.originalEvent.touches[0].pageX);
        const pageY = e.pageY || (e.originalEvent.touches && e.originalEvent.touches[0].pageY);
        
        translateX = pageX - startX;
        translateY = pageY - startY;
        updateImageTransform();
    });

    $(document).on('mouseup touchend', function() {
        if (isDragging) {
            isDragging = false;
            imgTarget.css('cursor', 'grab');
        }
    });

    // =================================================================
    // LAST PAGE TRACKING - LOGOUT CLEANUP
    // =================================================================
    const currentUserId = <?= function_exists('user_id') ? (user_id() ?? 'null') : 'null' ?>;
    if (currentUserId) {
        const redirectDoneKey = 'lastPageRedirectDone_' + currentUserId;
        const lastPageStorageKey = 'lastPage_' + currentUserId;

        $(document).on('click', 'a[href*="logout"], a[href*="/logout"]', function(e) {
            const currentPath = window.location.pathname;
            const skipPages = ['/login', '/logout', '/register', '/forgot', '/reset-password'];
            const shouldSkip = skipPages.some(page => currentPath.includes(page));
            
            // Simpan halaman sebelum keluar (selama bukan halaman auth)
            if (!shouldSkip) {
                localStorage.setItem(lastPageStorageKey, window.location.href);
            }
            // Hapus redirect flag
            sessionStorage.removeItem(redirectDoneKey);
        });
    }

    console.log('Backend scripts initialized');
});

// Global function for onclick="confirmDelete('url')"
function confirmDelete(url, text = 'Data akan dihapus permanen!') {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>
