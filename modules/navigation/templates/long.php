<nav class="module-navigation longnav">
	<ul class="nav nav-pills nav-stacked">
		<?php
		foreach( $this->items as $item ) {
			// TODO: Evtl. den aktiven Menüpunkt markieren...
			echo $item;
		}
		?>
	</ul>
</nav>
