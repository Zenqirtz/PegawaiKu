/**
 * ============================================================
 * assets/js/script.js
 * JavaScript PegawaiKu
 * SKKNI MUK JWD:
 * - Butir 1.b: Validasi Input Sisi Klien
 * - Butir 2.b: AJAX Request Menggunakan Library jQuery ($.ajax, $.getJSON)
 * - Interaksi Antarmuka: Toggle Sidebar Desktop & Mobile, Modal Quick View
 * ============================================================
 */

$(function () {
    'use strict';

    // Ambil path basis aplikasi dari meta tag (misal: '.' atau '..')
    const appBase = $('meta[name="app-base"]').attr('content') || '.';

    // ============================================================
    // 1. SIDEBAR COLLAPSE (Desktop & Mobile)
    // - Desktop: toggle class 'collapsed' (simpan state ke localStorage)
    // - Mobile: toggle class 'sidebar-open' + overlay
    // ============================================================
    const $sidebar        = $('#sidebar');
    const $collapseBtn    = $('#sidebarCollapseBtn');
    const $topbarToggle   = $('#sidebarMobileToggle');
    const $sidebarOverlay = $('#sidebarOverlay');
    const $sidebarHeader  = $('.sidebar-header');

    function isDesktop() {
        return window.innerWidth >= 992;
    }

    function applyDesktopCollapsed(collapsed) {
        if (collapsed) {
            $sidebar.addClass('collapsed');
            $('body').addClass('sidebar-collapsed');
            $collapseBtn.attr('title', 'Perluas Menu');
        } else {
            $sidebar.removeClass('collapsed');
            $('body').removeClass('sidebar-collapsed');
            $collapseBtn.attr('title', 'Ciutkan Menu');
        }
    }

    // Inisialisasi state sidebar desktop dari localStorage
    if (isDesktop()) {
        const savedState = localStorage.getItem('pegawaiku_sidebar_collapsed');
        if (savedState === 'true') {
            applyDesktopCollapsed(true);
        }
    }

    // Toggle collapse sidebar desktop via tombol di sidebar
    if ($collapseBtn.length) {
        $collapseBtn.on('click', function (e) {
            e.stopPropagation();
            if (isDesktop()) {
                const willCollapse = !$sidebar.hasClass('collapsed');
                applyDesktopCollapsed(willCollapse);
                localStorage.setItem('pegawaiku_sidebar_collapsed', willCollapse ? 'true' : 'false');
            } else {
                closeMobileSidebar();
            }
        });
    }

    // Klik header sidebar saat collapsed: buka kembali sidebar
    if ($sidebarHeader.length) {
        $sidebarHeader.on('click', function (e) {
            if (isDesktop() && $sidebar.hasClass('collapsed')) {
                if ($(e.target).closest('#sidebarCollapseBtn').length === 0) {
                    applyDesktopCollapsed(false);
                    localStorage.setItem('pegawaiku_sidebar_collapsed', 'false');
                }
            }
        });
    }

    // Toggle sidebar mobile atau buka kembali di desktop dari topbar
    if ($topbarToggle.length) {
        $topbarToggle.on('click', function () {
            if (isDesktop()) {
                const willCollapse = !$sidebar.hasClass('collapsed');
                applyDesktopCollapsed(willCollapse);
                localStorage.setItem('pegawaiku_sidebar_collapsed', willCollapse ? 'true' : 'false');
            } else {
                $sidebar.toggleClass('sidebar-open');
                $sidebarOverlay.toggleClass('show', $sidebar.hasClass('sidebar-open'));
                $('body').toggleClass('overflow-hidden', $sidebar.hasClass('sidebar-open'));
            }
        });
    }

    function closeMobileSidebar() {
        $sidebar.removeClass('sidebar-open');
        $sidebarOverlay.removeClass('show');
        $('body').removeClass('overflow-hidden');
    }

    if ($sidebarOverlay.length) {
        $sidebarOverlay.on('click', closeMobileSidebar);
    }

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeMobileSidebar();
    });

    // ============================================================
    // 2. FITUR AJAX JQUERY: CEK DUPLIKASI NIP REAL-TIME (SKKNI 2.b)
    // Memanggil endpoint api/cek_nip.php via $.ajax() saat input NIP
    // Menampilkan status ketersediaan secara langsung tanpa reload
    // ============================================================
    const $nipInput    = $('#nip');
    const $nipFeedback = $('#nipFeedback');
    let nipAjaxTimer   = null;
    let nipIsDuplicate = false;

    if ($nipInput.length && $nipFeedback.length) {
        const currentId = $nipInput.data('current-id') || 0;

        $nipInput.on('input blur', function () {
            const rawVal = $(this).val();
            const nipVal = $.trim(rawVal);

            clearTimeout(nipAjaxTimer);

            // Jika kosong, bersihkan pesan
            if (nipVal === '') {
                $nipFeedback.empty();
                $nipInput.removeClass('is-valid is-invalid');
                nipIsDuplicate = false;
                return;
            }

            // Tampilkan status memeriksa
            $nipFeedback.html(
                '<span class="text-secondary small">' +
                '<span class="spinner-border spinner-border-sm me-1" role="status"></span>' +
                'Memeriksa NIP via AJAX...' +
                '</span>'
            );

            // Debounce request AJAX agar tidak membebani server
            nipAjaxTimer = setTimeout(function () {
                $.ajax({
                    url: appBase + '/api/cek_nip.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        nip: nipVal,
                        exclude_id: currentId
                    },
                    success: function (response) {
                        if (response.status === 'duplicate') {
                            nipIsDuplicate = true;
                            $nipInput.addClass('is-invalid').removeClass('is-valid');
                            $nipFeedback.html(
                                '<span class="text-danger small fw-semibold">' +
                                '<i class="bi bi-x-circle-fill me-1"></i>' + escapeHtml(response.message) +
                                '</span>'
                            );
                        } else if (response.status === 'available') {
                            nipIsDuplicate = false;
                            $nipInput.removeClass('is-invalid').addClass('is-valid');
                            $nipFeedback.html(
                                '<span class="text-success small fw-semibold">' +
                                '<i class="bi bi-check-circle-fill me-1"></i>' + escapeHtml(response.message) +
                                '</span>'
                            );
                        } else {
                            nipIsDuplicate = false;
                            $nipInput.addClass('is-invalid').removeClass('is-valid');
                            $nipFeedback.html(
                                '<span class="text-warning small fw-semibold">' +
                                '<i class="bi bi-exclamation-triangle-fill me-1"></i>' + escapeHtml(response.message) +
                                '</span>'
                            );
                        }
                    },
                    error: function () {
                        $nipFeedback.html(
                            '<span class="text-muted small">' +
                            '<i class="bi bi-wifi-off me-1"></i> Tidak dapat terhubung ke endpoint validasi NIP.' +
                            '</span>'
                        );
                    }
                });
            }, 300);
        });
    }

    // ============================================================
    // 3. FITUR AJAX JQUERY: MODAL QUICK VIEW PEGAWAI (SKKNI 2.b)
    // Mengambil data lengkap 1 pegawai via $.getJSON() secara asinkron
    // Tampil di modal Bootstrap tanpa memuat ulang seluruh halaman
    // ============================================================
    const $quickViewModalEl = $('#quickViewModal');
    let quickViewModalInstance = null;

    if ($quickViewModalEl.length && typeof bootstrap !== 'undefined') {
        quickViewModalInstance = new bootstrap.Modal($quickViewModalEl[0]);
    }

    $(document).on('click', '.btn-quick-view', function (e) {
        e.preventDefault();
        const empId = $(this).data('id') || $(this).attr('data-id');
        if (!empId || !quickViewModalInstance) return;

        const $content = $('#quickViewContent');
        $content.html(
            '<div class="text-center py-4 text-secondary">' +
            '<div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>' +
            '<span>Memuat data pegawai via AJAX...</span>' +
            '</div>'
        );

        quickViewModalInstance.show();

        // AJAX Request via $.getJSON()
        $.getJSON(appBase + '/api/detail.php', { id: empId })
            .done(function (res) {
                if (res.status === 'success' && res.data) {
                    const d = res.data;
                    const html =
                        '<div class="text-center pb-3 border-bottom mb-3">' +
                            '<div class="quickview-avatar mx-auto mb-2">' + escapeHtml(d.initial) + '</div>' +
                            '<h5 class="fw-bold mb-1 text-dark">' + escapeHtml(d.nama) + '</h5>' +
                            '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">' +
                                escapeHtml(d.jabatan) +
                            '</span>' +
                        '</div>' +
                        '<div class="quickview-info-list">' +
                            '<div class="quickview-info-item">' +
                                '<span class="quickview-info-label"><i class="bi bi-person-vcard me-2 text-primary"></i>Nomor Induk Pegawai</span>' +
                                '<span class="quickview-info-value font-monospace fw-bold">' + escapeHtml(d.nip) + '</span>' +
                            '</div>' +
                            '<div class="quickview-info-item">' +
                                '<span class="quickview-info-label"><i class="bi bi-envelope me-2 text-primary"></i>Email Kedinasan</span>' +
                                '<span class="quickview-info-value">' +
                                    '<a href="mailto:' + escapeHtml(d.email) + '" class="text-decoration-none">' + escapeHtml(d.email) + '</a>' +
                                '</span>' +
                            '</div>' +
                            '<div class="quickview-info-item">' +
                                '<span class="quickview-info-label"><i class="bi bi-briefcase me-2 text-primary"></i>Jabatan / Posisi</span>' +
                                '<span class="quickview-info-value fw-semibold">' + escapeHtml(d.jabatan) + '</span>' +
                            '</div>' +
                            '<div class="quickview-info-item">' +
                                '<span class="quickview-info-label"><i class="bi bi-check-circle me-2 text-success"></i>Status Kepegawaian</span>' +
                                '<span class="quickview-info-value text-success fw-semibold"><i class="bi bi-patch-check-fill me-1"></i>Terdaftar Aktif</span>' +
                            '</div>' +
                        '</div>';
                    $content.html(html);
                } else {
                    $content.html('<div class="alert alert-danger py-2 mb-0">Data tidak ditemukan.</div>');
                }
            })
            .fail(function () {
                $content.html(
                    '<div class="alert alert-danger py-2 mb-0">' +
                    '<i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal mengambil data melalui AJAX.' +
                    '</div>'
                );
            });
    });

    function escapeHtml(text) {
        return $('<div>').text(text || '').html();
    }

    // ============================================================
    // 4. VALIDASI FORM PEGAWAI SISI KLIEN (Tambah & Edit)
    // ============================================================
    const $employeeForm = $('#formPegawai');

    if ($employeeForm.length) {
        $employeeForm.on('submit', function (event) {
            let isValid = true;

            const fields = {
                nip:     { el: $('#nip'),     check: v => v.trim() !== '' },
                nama:    { el: $('#nama'),    check: v => v.trim().length >= 2 },
                email:   { el: $('#email'),   check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) },
                jabatan: { el: $('#jabatan'), check: v => v.trim() !== '' },
            };

            $.each(fields, function (_, f) {
                if (!f.el.length) return;
                const valid = f.check(f.el.val());
                if (valid) {
                    f.el.removeClass('is-invalid');
                } else {
                    f.el.addClass('is-invalid');
                    isValid = false;
                }
            });

            // Cegah submit jika NIP terdeteksi duplikat oleh AJAX
            if (nipIsDuplicate) {
                $('#nip').addClass('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
                $employeeForm.find('.is-invalid').first().focus();
            }
        });
    }

    // ============================================================
    // 5. KONFIRMASI HAPUS DATA
    // ============================================================
    $(document).on('submit', '.form-hapus', function (event) {
        const nama = $(this).data('nama') || 'data ini';
        if (!confirm('Hapus data pegawai "' + nama + '"?\nTindakan ini tidak bisa dibatalkan.')) {
            event.preventDefault();
        }
    });
});
