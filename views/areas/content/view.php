<?php if( $this->editmode ) { ?>
    <?= \Toolbox\Tools\ElementBuilder::buildElementConfig('content', $this) ?>
<?php }?>
<div class="toolbox-content wysiwyg content-container <?= $this->select('contentAdditionalClasses')->getData();?>">
    <?= $this->template('toolbox/content.php') ?>
</div>