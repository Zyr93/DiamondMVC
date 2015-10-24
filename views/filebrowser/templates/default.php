<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * @param Snippet $this
 * 
 * {@link View} usable as a regular {@link Snippet} to display a file browser.
 * 
 * It expects a multidimensional array providing various data.
 * 1st level:
 *  - base:        Base ID of the filebrowser. Usually simply the relative path from root to the current directory.
 *  - url_getSize: URL from which to retrieve the file size if not already provided. This is to cut down unnecessary
 *                 processing time before sending out the response. Usually you won't have to adjust this if you
 *                 keep the above conformation.
 *  - files:       List of files to be shown in the filebrowser.
 * 
 * Each item in the files array ought to provide the following data:
 *  - is_dir: Whether the item represents a directory or a regular file
 *  - name:   Name of the item
 *  - id:     Unique ID. This is passed to the AJAX callback upon clicking the item.
 *  - size:   Size in bytes
 *  - perms:  File permissions
 */
defined('DIAMONDMVC') or die();

if( isset($this->controller) ) {
	$data = $this->controller->getResult();
}
else {
	$data = $this->getData();
}

$lang = i18n::load('diamondmvc');

$this->addStylesheet('filebrowser.css');
$this->addStylesheet('/assets/dropzone/dropzone.min.css');
$this->addScript('./filebrowser');
$this->addScript('dropzone');
?>
<div class="view view-filebrowser">
	<?php if( isset($data['controls']) and !empty($data['controls']) ) : { ?>
		<div class="filebrowser-controls pull-right">
			<?php if( isset($data['controls']['custom']) and !empty($data['controls']['custom']) ) : { ?>
				<?= $data['controls']['custom'] ?>
			<?php } endif ?>
			<?php if( isset($data['controls']['mkdir'])   and $data['controls']['mkdir'])    : { ?>
				<button class="btn btn-default filebrowser-control-mkdir" data-toggle="tooltip" data-placement="bottom" title="ctrl + m">
					<i class="fa fa-folder-o">&nbsp;</i> <?= $lang->get('LBL_NEW_DIR', 'ControllerFileBrowser') ?>
				</button>
			<?php } endif ?>
			<?php if( isset($data['controls']['move'])    and $data['controls']['move'])     : { ?>
				<button class="btn btn-default filebrowser-control-cut" data-toggle="tooltip" data-placement="bottom" title="ctrl + x">
					<i class="fa fa-scissors">&nbsp;</i> <?= $lang->get('CUT') ?>
				</button>
			<?php } endif ?>
			<?php if( isset($data['controls']['copy'])    and $data['controls']['copy'] )    : { ?>
				<button class="btn btn-default filebrowser-control-copy" data-toggle="tooltip" data-placement="bottom" title="ctrl + c">
					<i class="fa fa-files-o">&nbsp;</i> <?= $lang->get('COPY') ?>
				</button>
			<?php } endif ?>
			<?php if( (isset($data['controls']['move']) and $data['controls']['move']) or (isset($data['controls']['copy']) and $data['controls']['copy']) ) : { ?>
				<button class="btn btn-default filebrowser-control-paste" data-toggle="tooltip" data-placement="bottom" title="ctrl + v">
					<i class="fa fa-clipboard">&nbsp;</i> <?= $lang->get('PASTE') ?>
				</button>
			<?php } endif ?>
			<?php if( isset($data['controls']['rename'])  and $data['controls']['rename'] )  : { ?>
				<button class="btn btn-default filebrowser-control-rename" data-toggle="tooltip" data-placement="bottom" title="F2">
					<i class="fa fa-reply">&nbsp;</i> <?= $lang->get('RENAME') ?>
				</button>
			<?php } endif ?>
			<?php if( isset($data['controls']['refresh']) and $data['controls']['refresh'] ) : { ?>
				<button class="btn btn-default filebrowser-control-refresh" data-toggle="tooltip" data-placement="bottom" title="alt + r">
					<i class="fa fa-refresh">&nbsp;</i> <?= $lang->get('REFRESH') ?>
				</button>
			<?php } endif ?>
			<?php if( isset($data['controls']['delete'])  and $data['controls']['delete'] )  : { ?>
				<button class="btn btn-danger filebrowser-control-delete" data-toggle="tooltip" data-placement="bottom" title="del">
					<i class="fa fa-trash">&nbsp;</i> <?= $lang->get('DELETE') ?>
				</button>
			<?php } endif ?>
			<?php if( isset($data['controls']['upload'])  and $data['controls']['upload'] )  : { ?>
				<button class="btn btn-primary filebrowser-control-upload" data-toggle="tooltip" data-placement="bottom" title="ctrl + u">
					<i class="fa fa-upload">&nbsp;</i> <?= $lang->get('UPLOAD') ?>
				</button>
			<?php } endif ?>
		</div>
	<?php } endif ?>
	<table class="filebrowser" data-base="<?= $data['base'] ?>" data-action-url="<?= $data['actionUrl'] ?>">
		<thead>
			<tr>
				<th style="padding-left:23px"><?= $lang->get('NAME') ?></th>
				<th><?= $lang->get('SIZE') ?></th>
				<th><?= $lang->get('PERMS') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if( $data['up_dir'] ) : { ?>
				<tr data-id="..">
					<td class="file-info-name"><i class="fa fa-folder-o">&nbsp;</i> ..</td>
					<td></td>
					<td></td>
				</tr>
			<?php } endif ?>
			<?php if( empty($data['files']) ) : { ?>
				<tr>
					<td colspan="3"><?= $lang->get('NO_FILES_FOUND', 'ControllerFileBrowser') ?></td>
				</tr>
			<?php } else : { ?>
				<?php foreach( $data['files'] as $file ) : { ?>
					<tr data-id="<?= $file['id'] ?>">
						<td class="file-info-name">
							<?php if( $file['is_dir'] ) : { ?>
								<i class="fa fa-folder-o">&nbsp;</i>
							<?php } else : { ?>
								<i class="fa fa-file-text-o">&nbsp;</i>
							<?php } endif ?>
							
							<?= $file['name'] ?>
						</td>
						<td class="file-info-size"><?= empty($file['size']) ? '?' : $file['size'] ?></td>
						<td class="file-info-perms"><?= $file['perms'] ?></td>
					</tr>
				<?php } endforeach ?>
			<?php } endif ?>
		</tbody>
	</table>
	
	<div class="modals">
		<div class="modal fade modal-rename">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Rename files</h4>
					</div>
					<div class="modal-body">
						<p><?= $lang->get('MODAL_RENAME_DESC', 'ControllerFileBrowser') ?></p>
						<input type="text" class="form-control" placeholder="<?= $lang->get('MODAL_RENAME_NEW_NAME_PLACEHOLDER', 'ControllerFileBrowser') ?>">
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal"><?= $lang->get('ABORT') ?></button>
						<button type="button" class="btn btn-primary modal-btn-confirm"><?= $lang->get('CONFIRM') ?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade modal-mkdir">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Create new directory</h4>
					</div>
					<div class="modal-body">
						<p><?= $lang->get('MODAL_MKDIR_DESC', 'ControllerFileBrowser') ?></p>
						<input type="text" class="form-control" placeholder="<?= $lang->get('MODAL_MKDIR_TARGET_NAME_PLACEHOLDER', 'ControllerFileBrowser') ?>">
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal"><?= $lang->get('ABORT') ?></button>
						<button type="button" class="btn btn-primary modal-btn-confirm"><?= $lang->get('CONFIRM') ?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade modal-upload">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Upload file</h4>
					</div>
					<div class="modal-body">
						<form action="<?= $data['actionUrl'] ?>/upload" class="clickable">
							<div class="fallback">
								<input type="file" name="file">
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary modal-btn-confirm" data-dismiss="modal"><?= $lang->get('CLOSE') ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
