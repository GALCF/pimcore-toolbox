<?php if ($this->download instanceof \Pimcore\Model\Asset\Folder) { ?>

    <?php foreach($this->download->getChilds() as $d) { ?>
        <?= $this->template('toolbox/download/list/item.php', array('download' => $d)) ?>
    <?php } ?>

<?php } ?>
<?php

$dPath = $this->download->getFullPath();
$dSize = $this->download->getFileSize('kb', 2);
$dType = Pimcore\File::getFileExtension($this->download->getFilename());
$dName = ($this->download->getMetadata('title')) ? $this->download->getMetadata('title') : $this->translate('Download');

if ( $this->showPreviewImages ) {

    $dPreview = $this->download->getMetadata('previewImage') instanceof \Pimcore\Model\Asset\Image
        ? $this->download->getMetadata('previewImage')->getThumbnail('downloadPreviewImage')
        : (
        $this->download instanceof \Pimcore\Model\Asset\Image
            ? $this->download->getThumbnail('downloadPreviewImage')
            : $this->download->getImageThumbnail('downloadPreviewImage')
        );

    $altText = $this->download->getMetadata('alt') ? $this->download->getMetadata('alt') : $dName;
}
?>

<li>
    <a href="<?= $dPath; ?>" <?= $this->toolboxHelper()->addTracker('download', $this->download); ?> target="_blank" class="icon-download-<?= $dType; ?>">
        <?php if ( $this->showPreviewImages ) {?><span class="preview-image"><img src="<?=$dPreview?>" alt="<?=$altText?>" /></span><?php } ?>
        <span class="title"><?=$dName; ?></span>
        <?php if ( $this->showFileInfo ) { ?> <span class="file-info">(<span class="file-type"><?=$dType?></span>, <?=$dSize?>)</span><?php } ?>
    </a>
</li>