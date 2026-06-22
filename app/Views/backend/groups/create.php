<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Masukkan Nama Group Baru</h3>
            </div>
            
            <form action="<?= base_url('backend/groups/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Group</label>
                        <input type="text" class="form-control <?= isset(session('errors')['name']) ? 'is-invalid' : '' ?>" 
                               id="name" name="name" placeholder="Misal: Keluarga Deni & Indra" value="<?= old('name') ?>" required>
                        <?php if (isset(session('errors')['name'])): ?>
                            <div class="invalid-feedback">
                                <?= session('errors')['name'] ?>
                            </div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Grup digunakan untuk mengelompokkan beberapa trip perjalanan dan anggota keluarga/teman.</small>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan Group</button>
                    <a href="<?= base_url('backend/groups') ?>" class="btn btn-default float-right">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
