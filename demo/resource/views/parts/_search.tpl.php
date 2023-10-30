<div class="col-md-6 col-xs-12">
    <form method="POST" action="<?php echo $this->self['url']; ?>" role="form"
          class="form-inline ajax pull-right search-form" data-root="#<?php echo $this->self['id']; ?>">
        <div class="form-group input-group">
            <span class="input-group-addon">Name</span>
            <input id="name" name="name" class="form-control" placeholder="ex. Item 2">
            <span class="input-group-btn">
                <button type="submit" name="search" value="1" class="btn btn-default">Show</button>
                <button type="submit" name="create" value="1" class="btn btn-default">Add</button>
            </span>
        </div>
    </form>
</div>