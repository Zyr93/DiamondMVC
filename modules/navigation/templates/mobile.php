<?php
$this->addStylesheet('mobile-nav.css');
?>
<nav class="module-navigation mobile">
	<ul>
		<?php
		foreach( $this->items as $item ) {
			echo $item;
		}
		?>
	</ul>
</nav>
