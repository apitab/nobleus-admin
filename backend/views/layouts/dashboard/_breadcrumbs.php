<div class="d-sm-flex align-items-center justify-content-between mg-b-10 mg-lg-b-15 mg-xl-b-20">
    <div>
        <?php if(!isset($show_breadcrumbs) || (isset($show_breadcrumbs) && $show_breadcrumbs)) : ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                <li class="breadcrumb-item"><a href="<?= Yii::$app->homeUrl; ?>"><?= Yii::t('app','Home'); ?></a></li>
                <?php foreach ($links as $link) : ?>
                    <?php if (isset($link['active'])) : ?>
                        <li class="breadcrumb-item active"><?= $link['title']; ?></li>
                    <?php else : ?>
                        <li class="breadcrumb-item"><a href="<?= $link['url']; ?>"><?= $link['title']; ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
        <h4 class="mg-b-0 tx-spacing--1"><?= $title; ?></h4>
        <?php if(isset($sub_title)):?>
            <p><?= $sub_title; ?></p>
        <?php endif;?>    
    </div>
    <div class="d-none d-md-block">
        <?php if (isset($buttons)) : ?>
            <?php foreach ($buttons as $button) : ?>
                <a href="<?= $button['link'] ?>" class="btn btn-sm pd-x-15 btn-primary btn-uppercase mg-l-5"><i data-feather="<?= $button['icon'] ?>" class="wd-10 mg-r-5"></i> <?= $button['title'] ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>