<?php
if(isset($menuItems)):
?>
<ul id="mainMenu" class="menu">
	<?php echo $this->Menu->build($menuItems);?>
</ul>
<?php
endif;
?>