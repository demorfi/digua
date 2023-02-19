<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>Digua Instance | <?php echo $this->title; ?></title>
    <link rel="stylesheet" href="/stylesheet/bootstrap.min.css"/>
    <link rel="stylesheet" href="/stylesheet/font-awesome.min.css"/>
    <link rel="stylesheet" href="/stylesheet/main.css"/>
    <?php foreach ($this->styles() as $style) { ?>
        <link rel="stylesheet" href="/stylesheet/<?php echo $style; ?>.css"/>
    <?php } ?>
    <script defer src="/javascript/jquery.min.js"></script>
    <script defer src="/javascript/jquery.storageapi.min.js"></script>
    <script defer src="/javascript/bootstrap.min.js"></script>
    <script defer src="/javascript/main.js"></script>
    <?php foreach ($this->javascripts() as $javascript) { ?>
        <script src="/javascript/<?php echo $javascript; ?>.js" type="module"></script>
    <?php } ?>
</head>
<body class="bg-success">
<?php echo $this->block('header'); ?>

<div class="container">
    <div class="page-header">
        <?php if ($this->hasBlock('page-header')) { ?>
            <?php echo $this->block('page-header'); ?>
        <?php } else { ?>
            <h1>
                <i class="fa fa-pagelines fa-<?php echo strtolower($this->title); ?>"></i>
                <?php echo $this->name ?: $this->title; ?>
            </h1>
        <?php } ?>
    </div>
    <div class="alert alert-dismissible alert-messages hidden" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <span class="message"></span>
    </div>
    <?php echo $this->block('content'); ?>
</div>

<footer class="footer">
    <div class="container">
        <p class="text-muted"><i class="fa fa-code"></i> with <i class="fa fa-heart"></i> in Siberia.</p>
    </div>
</footer>
</body>
</html>
