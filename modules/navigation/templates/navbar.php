<nav class="module-navigation navbar navbar-default" role="navigation">
	<div id="navbar-header">
		<button type="button" data-target="#navbar-content" data-toggle="collapse" class="navbar-toggle">
			<span class="sr-only">Toggle menu</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
	</div>
	<div id="navbar-content" class="collapse navbar-collapse">
		<ul class="nav navbar-nav navbar-left">
			<?php foreach( $this->items as $item ) {
				echo $item;
			} ?>
		</ul>
		<?php if( count($this->itemsRight) ) : { ?>
			<ul class="nav navbar-nav navbar-right">
				<?php foreach( $this->itemsRight as $item ) {
					echo $item;
				} ?>
			</ul>
		<?php } endif ?>
	</div>
</nav>

