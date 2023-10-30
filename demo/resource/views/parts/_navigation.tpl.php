<nav aria-label="navigation">
    <ul class="pagination pull-left <?php echo !$this->pagination->hasPages() ? ' hidden ' : ''; ?>"
        data-url="<?php echo $this->self['url']; ?>" data-root="#<?php echo $this->self['id']; ?>">
        <li class="pagination-prev <?php echo !$this->pagination->hasPrev() ? ' disabled ' : ''; ?>">
            <a href="<?php echo $this->pagination->getPrevPage($this->self['url']); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php foreach ($this->pagination->getNavigation($this->self['url']) as $navigation) { ?>
            <li class="<?php echo $navigation['active'] ? 'active' : ''; ?>">
                <a href="<?php echo($navigation['url']); ?>">
                    <?php echo($navigation['page']); ?>
                </a>
            </li>
        <?php } ?>
        <li class="pagination-next <?php echo !$this->pagination->hasNext() ? ' disabled ' : ''; ?>">
            <a href="<?php echo $this->pagination->getNextPage($this->self['url']); ?>"
               aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>