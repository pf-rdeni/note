<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Kegiatan Baru</h3>
            </div>
            
            <form action="<?= base_url('backend/trips/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <?php if (empty($groups)): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Anda harus menjadi **Group Admin** di minimal satu grup untuk dapat membuat kegiatan. Silakan buat grup baru terlebih dahulu.
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="group_id">Pilih Kelompok (Group)</label>
                            <select class="form-control select2" style="width: 100%;" id="group_id" name="group_id" required>
                                <option value="" disabled selected>-- Pilih Group --</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= old('group_id') == $g['id'] ? 'selected' : '' ?>><?= esc($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Kegiatan hanya bisa ditambahkan ke group di mana Anda memiliki hak akses Admin.</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Nama Kegiatan</label>
                            <input type="text" class="form-control <?= isset(session('errors')['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" placeholder="Misal: Belanja Mingguan, Uang Kas, Jajan Ngopi" value="<?= old('name') ?>" required>
                            <?php if (isset(session('errors')['name'])): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors')['name'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai (Opsional)</label>
                                    <input type="date" class="form-control <?= isset(session('errors')['start_date']) ? 'is-invalid' : '' ?>" 
                                           id="start_date" name="start_date" value="<?= old('start_date') ?>">
                                    <?php if (isset(session('errors')['start_date'])): ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors')['start_date'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai (Opsional)</label>
                                    <input type="date" class="form-control <?= isset(session('errors')['end_date']) ? 'is-invalid' : '' ?>" 
                                           id="end_date" name="end_date" value="<?= old('end_date') ?>">
                                    <?php if (isset(session('errors')['end_date'])): ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors')['end_date'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control <?= isset(session('errors')['notes']) ? 'is-invalid' : '' ?>" 
                                      id="notes" name="notes" rows="3" placeholder="Detail rencana kegiatan atau lainnya..."><?= old('notes') ?></textarea>
                            <?php if (isset(session('errors')['notes'])): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors')['notes'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <?php if (!empty($groups)): ?>
                        <button type="submit" class="btn btn-primary">Simpan Kegiatan</button>
                    <?php endif; ?>
                    <a href="<?= base_url('backend/trips') ?>" class="btn btn-default float-right">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
});
</script>
<?= $this->endSection() ?>
