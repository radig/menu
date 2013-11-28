<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            
            <a class="brand" href="/"><?php echo Configure::read('Project'); ?></a>
            
            <div class="nav-collapse">
                <?php if(isset($menuItems)): ?>
                    <ul id="mainMenu" class="nav">
                        <?php echo $this->Menu->render($menuItems);?>
                    </ul>
                    
                <?php endif; ?>
                
                <?php if(isset($menuConfigureItems)): ?>
                    <ul id="configureMenu" class="nav pull-right">
                        <?php echo $this->Menu->render($menuConfigureItems);?>
                    </ul>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>
