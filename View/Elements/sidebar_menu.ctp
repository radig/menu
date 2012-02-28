<?php
$menuRelation = Configure::read('Radig.Menu.Relation');
if(isset($menuSidebarItems) && isset($menuRelation[$this->request->here])): 
?>
	<div id="offset" class="span1"></div>
	<div id="sidebar" class="span2">
		<?php if(!empty($activeUser) && isset($menuSidebarItems)): ?>
			<div class="well" style="padding: 8px 0;">
				<ul class="nav nav-list">
					<?php echo $this->Menu->renderSide($menuSidebarItems);?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
<?php
endif;
?>