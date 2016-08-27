
<div id="sacloudojs-flash" class="updated">
		<p></p>
		<?php if($messages): ?>
				<?php foreach($messages as $msg): ?>
						<p><?php echo $msg; ?></p>
				<?php endforeach; ?>
		<?php endif; ?>
</div>

<?php if(isset($_POST['resync']) && $_POST['resync']): ?>
<div id="sacloudojs-resync-status" class="updated">
		<?php _e('Resynchronization result.', "wp-sacloud-ojs"); ?>
		<?php if($messages): ?>
				<?php foreach($messages as $msg): ?>
						<p><?php echo $msg; ?></p>
				<?php endforeach; ?>
		<?php endif; ?>
</div>
<?php endif; ?>

<h2><?php _e('Setting SakuraCloud ObjectStorage', "wp-sacloud-ojs"); ?></h2>

<p><?php _e("Please Input the API tokens for the SakuraCloud ObjectStorage. No account? Let's ", 'wp-sacloud-ojs'); ?><a href="<?php _e('https://secure.sakura.ad.jp/member-regist/input', 'wp-sacloud-ojs'); ?>" target="_blank" ><?php _e('signup', 'wp-sacloud-ojs'); ?></a></p>

<form method="post" action="options.php">
		<?php settings_fields('sacloudojs-options'); ?>
		<?php do_settings_sections('sacloudojs-options'); ?>
		<table>
				<tr>
						<th><?php _e('ObjectStorage AccessKey', 'wp-sacloud-ojs') ?>:</th>
						<td>
								<input id="sacloudojs-accesskey" name="sacloudojs-accesskey" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('sacloudojs-accesskey')
																				 ); ?>" class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('ObjectStorage Secret', 'wp-sacloud-ojs') ?>:</th>
						<td>
								<input id="sacloudojs-secret" name="sacloudojs-secret" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('sacloudojs-secret')
																				 ); ?>"  class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('Bucket Name', 'wp-sacloud-ojs') ?>:</th>
						<td>
								<input id="sacloudojs-bucket" name="sacloudojs-bucket" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('sacloudojs-bucket')
																				 ); ?>" class="regular-text code"/>

						</td>
				</tr>
<?php
/*
				<tr>
						<th><?php _e('Container Name', 'wp-sacloud-ojs') ?>:</th>
						<td>
								<input id="saclousojs-container" name="sacloudojs-container" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('sacloudojs-container')
																				 ); ?>" class="regular-text code"/>
						</td>
				</tr>
 */
?>
				<tr>
					<th><?php _e('Use SSL', 'wp-sacloud-ojs') ?>:</th>
					<td >
						<input id="sacloudojs-use-ssl" type="checkbox" name="sacloudojs-use-ssl"
							   value="1" <?php checked(get_option('sacloudojs-use-ssl'),1); ?> />
						<label for="sacloudojs-use-ssl"><?php _e('Use SSL protocol', 'wp-sacloud-ojs'); ?></label>
					</td>
				</tr>

				<tr>
					<th><?php _e('Use cache', 'wp-sacloud-ojs') ?>:</th>
					<td >
						<input id="use-cache" type="checkbox" name="sacloudojs-use-cache"
							   value="1" <?php checked(get_option('sacloudojs-use-cache'),1); ?> />
						<label for="use-cache"><?php _e('Use Cache URL', 'wp-sacloud-ojs'); ?></label>
					</td>
				</tr>
				<tr>
						<td colspan="2" style="padding-top: 1em">
                <input type="button" name="test" id="submit" class="button button-secondary"
                        value="<?php _e('Check the connection', 'wp-sacloud-ojs'); ?>"
												onclick="sacloudojs_connect_test()"/>
						</td>
				</tr>
		</table>
<?php
/*
		<h3><?php _e('File Types', 'wp-sacloud-ojs'); ?></h3>
		<table>
				<tr>
						<th><?php _e('Extensions', 'wp-sacloud-ojs') ?>:</th>
						<td>
								<input id="sacloudojs-extensions" name="sacloudojs-extensions" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('sacloudojs-extensions')
																				 ); ?>" class="regular-text code"/>

								<p class="description"><?php _e('The media files that has these extensions will be uploaded to the Object Storage. You can use comma separated format to specify more than one(Example: "png,jpg,gif,mov,wmv").', 'wp-sacloud-ojs'); ?></p>
								<p class="description"><?php _e('If this field is blank, Everything will be uploaded.', 'wp-sacloud-ojs'); ?></p>

						</td>
				</tr>
		</table>
 */
?>
		<table>
				<tr>
						<td colspan="2">
								<?php submit_button(); ?>
						</td>
				</tr>
		</table>

</form>

<hr />
<h2><?php _e('Resynchronization', "wp-sacloud-ojs"); ?></h2>
<form  method="post">
     <p><?php _e('Resynchronization all media files to the Object Storage. It may take a long time.', 'wp-sacloud-ojs') ?></p>
    <?php submit_button(__('Resynchronization', "wp-sacloud-ojs"), 'Secondary', 'resync') ?>
</form>
