<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#top-navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">Digua Instance</a>
        </div>

        <div class="collapse navbar-collapse" id="top-navbar">
            <ul class="nav navbar-nav">
                <li class="<?php echo $this->hasRoute('page1') ? ' active ' : ''; ?>">
                    <a href="/page1">
                        <i class="fa fa-pagelines"></i> Page 1
                    </a>
                </li>
                <li class="<?php echo $this->hasRoute('page2') ? ' active ' : ''; ?>">
                    <a href="/page2">
                        <i class="fa fa-pagelines"></i> Page 2
                    </a>
                </li>
            </ul>

            <form method="POST" action="/" role="form" class="navbar-form navbar-right ajax"
                  data-root="body > .container">
                <input type="hidden" name="check" value="1"/>
                <button type="submit" class="btn btn-info">Check Service</button>
            </form>
        </div>
    </div>
</div>
