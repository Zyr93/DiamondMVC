<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * @param View $this
 */
defined('DIAMONDMVC') or die();

$result = $this->controller->getResult();
?>
<div class="view view-test view-temp-test">
	<?php var_dump($result) ?>
</div>
