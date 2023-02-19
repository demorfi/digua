<?php
$this->title = 'Not Found';
$this->extend('layout');
$this->shortSection('header', 'parts/_menu');
?>

<?php $this->section('content'); ?>
<div>
    Not Found!
</div>
<?php $this->endSection('content'); ?>
