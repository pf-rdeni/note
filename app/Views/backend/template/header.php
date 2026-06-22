<!-- Content Header (Page Header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <!-- Gunakan Class text-truncate atau kurangi size -->
                <h4 class="m-0 text-dark"><?= esc($pageTitle ?? 'Dashboard') ?></h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <?php if ($index === count($breadcrumb) - 1): ?>
                                <li class="breadcrumb-item active"><?= esc($item['title']) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= base_url($item['url']) ?>"><?= esc($item['title']) ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
                        <li class="breadcrumb-item active"><?= esc($pageTitle ?? 'Dashboard') ?></li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
    </div>
</section>
