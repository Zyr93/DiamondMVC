<?php
defined('DIAMONDMVC') or die;
$version1 = Version::parse('1.4.2');
$version2 = Version::parse('1.4.2.123');
$version3 = Version::parse('1.2.6');
$version4 = Version::parse('1.6.8');
$version5 = Version::parse('2');
?>
<div class="view view-unittest" id="unittest-versioning">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Test</th>
				<th>Expected result</th>
				<th>Actual result</th>
				<th>Pass?</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?= "$version1 < $version2" ?></td>
				<td>true</td>
				<td><?= ($result = $version1->lessThan($version2)) ? 'true' : 'false' ?></td>
				<td>
					<?php if( $result ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td><?= "$version1 > $version5" ?></td>
				<td>false</td>
				<td><?= ($result = $version1->greaterThan($version5)) ? 'true' : 'false' ?></td>
				<td>
					<?php if( !$result ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td><?= "$version1 = $version1" ?></td>
				<td>true</td>
				<td><?= ($result = ($version1->compareTo($version1) === 0)) ? 'true' : 'false' ?></td>
				<td>
					<?php if( $result ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td><?= "$version2 > $version3" ?></td>
				<td>true</td>
				<td><?= ($result = $version2->greaterThan($version3)) ? 'true' : 'false' ?></td>
				<td>
					<?php if( $result ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
