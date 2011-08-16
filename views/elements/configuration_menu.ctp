<?php
echo $this->Html->link(
	__('Configurações', true),
	'#configMenu',
	array('class' => 'button-app-build button user-button jsPopupMenuLink')
);

$script = <<<SCRIPT
	var menu = '<ul id="configMenu" class="popupMenu">'
			+  '{$this->Menu->build($configMenu)}'
			+  '</ul>';
			
	$(document).ready(function () {
		$('#popupMenuContainer').append(menu);
	});
			
	delete menu;
SCRIPT;

echo $this->Html->scriptBlock($script);
?>