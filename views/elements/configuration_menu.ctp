<?php
echo $this->Html->link(
	__('Configurações', true),
	'#',
	array('class' => 'button-app-build button user-button jsConfigMenuLink')
);
?>
<div id="configMenuContainer">
	<ul id="configMenu">
		<?php echo $this->Menu->build($configMenu); ?>
	</ul>
</div>