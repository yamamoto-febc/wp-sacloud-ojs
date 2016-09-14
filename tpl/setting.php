<div id="sacloudojs-flash" class="notice">
    <p></p>
</div>

<h2><?php _e('Setting SakuraCloud ObjectStorage', "wp-sacloud-ojs"); ?></h2>

<p><?php _e("Please Input the API tokens for the SakuraCloud ObjectStorage. No account? Let's ", 'wp-sacloud-ojs'); ?><a
        href="<?php _e('https://secure.sakura.ad.jp/member-regist/input', 'wp-sacloud-ojs'); ?>"
        target="_blank"><?php _e('signup', 'wp-sacloud-ojs'); ?></a></p>

<form method="post" action="options.php">
    <?php settings_fields('sacloudojs-options'); ?>
    <?php do_settings_sections('sacloudojs-options'); ?>

    <h3><?php _e('Basic settings', 'wp-sacloud-ojs'); ?></h3>
    <table>
        <tr>
            <th><?php _e('ObjectStorage AccessKey', 'wp-sacloud-ojs') ?>:</th>
            <td>
                <input id="sacloudojs-accesskey" name="sacloudojs-options[AccessKey]" type="text"
                       size="15" value="<?php echo esc_attr(
                    Wp_Sacloud_Ojs\Options::$Instance->AccessKey
                ); ?>" class="regular-text code"/>

            </td>
        </tr>
        <tr>
            <th><?php _e('ObjectStorage Secret', 'wp-sacloud-ojs') ?>:</th>
            <td>
                <input id="sacloudojs-secret" name="sacloudojs-options[Secret]" type="text"
                       size="15" value="<?php echo esc_attr(
                    Wp_Sacloud_Ojs\Options::$Instance->Secret
                ); ?>" class="regular-text code"/>

            </td>
        </tr>
        <tr>
            <th><?php _e('Bucket Name', 'wp-sacloud-ojs') ?>:</th>
            <td>
                <input id="sacloudojs-bucket" name="sacloudojs-options[Bucket]" type="text"
                       size="15" value="<?php echo esc_attr(
                    Wp_Sacloud_Ojs\Options::$Instance->Bucket
                ); ?>" class="regular-text code"/>

            </td>
        </tr>

        <tr>
            <th><?php _e('Use SSL', 'wp-sacloud-ojs') ?>:</th>
            <td>
                <input id="sacloudojs-use-ssl" type="checkbox" name="sacloudojs-options[UseSSL]"
                       value="1" <?php checked(Wp_Sacloud_Ojs\Options::$Instance->UseSSL, 1); ?> />
                <label for="sacloudojs-use-ssl"><?php _e('Use SSL protocol', 'wp-sacloud-ojs'); ?></label>
            </td>
        </tr>

        <tr>
            <th><?php _e('Use cache', 'wp-sacloud-ojs') ?>:</th>
            <td>
                <input id="sacloud-use-cache" type="checkbox" name="sacloudojs-options[UseCache]"
                       value="1" <?php checked(Wp_Sacloud_Ojs\Options::$Instance->UseCache, 1); ?> />
                <label for="sacloud-use-cache"><?php _e('Use Cache URL', 'wp-sacloud-ojs'); ?></label>
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

    <h3><?php _e('Advance settings', 'wp-sacloud-ojs'); ?></h3>
    <table>
        <tr>
            <th><?php _e('Delete file', 'wp-sacloud-ojs') ?>:</th>
            <td>
                <input id="sacloudojs-delete-object" type="checkbox" name="sacloudojs-options[DeleteObject]"
                       value="1" <?php checked(Wp_Sacloud_Ojs\Options::$Instance->DeleteObject, 1); ?> />
                <label for="sacloudojs-delete-object"><?php _e('Delete file on server', 'wp-sacloud-ojs'); ?></label>
                <p class="description"><?php _e('Delete file on the WordPress server after uploaded to ObjectStorage', 'wp-sacloud-ojs') ?></p>
            </td>
        </tr>
    </table>


    <table>
        <tr>
            <td colspan="2">
                <?php submit_button(); ?>
            </td>
        </tr>
    </table>

</form>