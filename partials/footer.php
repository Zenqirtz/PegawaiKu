        <!-- Akhir dari main-content (dibuka di header.php) -->
        </main>

    </div><!-- Akhir content-wrapper -->

</div><!-- Akhir wrapper (d-flex) -->

<!-- ============================================================
     MODAL QUICK VIEW (AJAX jQuery)
     Satu modal dipakai bersama oleh semua halaman.
     Isinya (#quickViewContent) diisi via $.getJSON() tanpa reload.
     ============================================================ -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content quickview-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title quickview-title" id="quickViewLabel">
                    <i class="bi bi-person-vcard me-2 text-primary"></i> Detail Cepat Pegawai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-2" id="quickViewContent">
                <div class="text-center py-4 text-secondary">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Memuat data...
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (Library Pre-Existing JS untuk kemudahan DOM & AJAX - Sesuai SKKNI MUK JWD) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS (diperlukan untuk komponen interaktif Bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS custom aplikasi (validasi form, sidebar toggle, konfirmasi hapus) -->
<script src="<?= $base ?>/assets/js/script.js?v=<?= filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
