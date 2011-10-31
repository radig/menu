<?php
if(isset($menuItems)):
?>
<ul id="mainMenu" class="menu">
	<?php echo $this->Menu->render($menuItems);?>
</ul>
<?php
endif;
?>