<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="brand" href="#"><?php echo Configure::read('Project'); ?></a>
			<?php if(isset($menuItems)): ?>
			<ul id="mainMenu" class="nav">
				<?php echo $this->Menu->render($menuItems);?>
			</ul>
			<form class="navbar-search pull-left">
				<input type="text" class="search-query" placeholder="Search">
			</form>
			<?php endif; ?>
			<?php if(isset($menuConfigureItems)): ?>
			<ul id="configureMenu" class="nav pull-right">
				<?php echo $this->Menu->render($menuConfigureItems);?>
			</ul>
			<?php endif; ?>
		</div>
	</div>
</div>