<?php
$this->extend('layout');
$this->shortSection('header', 'parts/_menu');
?>

<?php $this->section('content'); ?>
<div>

    <!-- Nav tabs -->
    <ul class="nav nav-pills" role="tablist">
        <li role="presentation" class="<?php echo $this->hasRoute('page1.list1') ? ' active ' : ''; ?>">
            <a href="#page1-list1" aria-controls="page1-list1" role="tab"
               data-toggle="tab" data-url="/page1/list1">List 1</a>
        </li>
        <li role="presentation" class="<?php echo $this->hasRoute('page1.list2') ? ' active ' : ''; ?>">
            <a href="#page1-list2" aria-controls="page1-list2" role="tab"
               data-toggle="tab" data-url="/page1/list2">List 2</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">

        <!-- Tab 1 -->
        <div role="tabpanel" class="tab-pane <?php echo $this->hasRoute('page1.list1') ? ' active ' : ''; ?>"
             id="page1-list1">
            <div class="panel panel-default clearfix">
                <div class="row">
                    <div class="col-md-6 col-xs-12">

                        <!-- Navigation pages -->
                        <?php $this->view('parts/_navigation', ['id' => 'page1-list1', 'url' => '/page1/list1']) ?>
                    </div>

                    <!-- Search -->
                    <?php $this->view('parts/_search', ['id' => 'page1-list1', 'url' => '/page1/list1']) ?>
                </div>

                <!-- Items -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>Name</th>
                    </tr>
                    </thead>
                    <tbody class="output">
                    <?php if ($this->hasRoute('page1.list1')) { ?>
                        <?php if (sizeof($this->list)) { ?>
                            <?php foreach ($this->list as $item) { ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo $item['name']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="3">Has not any items</td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2 -->
        <div role="tabpanel" class="tab-pane <?php echo $this->hasRoute('page1.list2') ? ' active ' : ''; ?>"
             id="page1-list2">
            <div class="panel panel-default clearfix">
                <div class="row">
                    <div class="col-md-6 col-xs-12">

                        <!-- Navigation pages -->
                        <?php $this->view('parts/_navigation', ['id' => 'page1-list2', 'url' => '/page1/list2']) ?>
                    </div>

                    <!-- Search -->
                    <?php $this->view('parts/_search', ['id' => 'page1-list2', 'url' => '/page1/list2']) ?>
                </div>

                <!-- Items -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>Name</th>
                    </tr>
                    </thead>
                    <tbody class="output">
                    <?php if ($this->hasRoute('page1.list2')) { ?>
                        <?php if (sizeof($this->list)) { ?>
                            <?php foreach ($this->list as $item) { ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo $item['name']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="3">Has not any items</td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection('content'); ?>
