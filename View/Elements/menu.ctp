<?php
if(isset($menuItems)):
?>
<ul id="mainMenu" class="menu<?php echo isset($vertical)? ' menu_vertical' : ' menu_horizontal';?>">
	<?php echo $this->Menu->render($menuItems);?>
</ul>
<?php
endif;
?>