<?php

/**
 * Plugin Name: wp-sacloud-ojs
 * Plugin URI: https://github.com/yamamoto-febc/wp-sacloud-ojs
 * Description: WordPressのメディアファイル(画像など)をさくらのクラウドのオブジェクトストレージで扱うためのプラグイン
 * Author: Kazumichi Yamamoto
 * Author URI: https://github.com/yamamoto-febc
 * Text Domain: wp-sacloud-ojs
 * Version: 0.0.8
 * License: GPLv2
 */

// Load SDKs
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// use OpenCloud\Openstack namespace
use Aws\S3\S3Client;

// -------------------- Register boot functions ---------------------
register_deactivation_hook(__FILE__, 'sacloudojs_deactivate');
register_uninstall_hook(__FILE__, 'sacloudojs_uninstall');
add_action('init', 'sacloudojs_start');
// ------------------------------------------------------------------

function sacloudojs_start()
{
    // Text Domain
    load_plugin_textdomain('wp-sacloud-ojs', false, basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lang');

    add_action('admin_menu', 'add_pages');
    add_action('admin_init', 'sacloudojs_options');
    add_action('wp_ajax_sacloudojs_connect_test', 'sacloudojs_connect_test');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'sacloudojs_add_action_links');

    if (sacloudojs_client_auth()) {

        add_action('add_attachment', 'sacloudojs_upload_file');
        add_action('edit_attachment', 'sacloudojs_upload_file');
        add_action('delete_attachment', 'sacloudojs_delete_object_by_id');

        add_action("updated_postmeta", "sacloud_after_image_edit", 10, 4);
        add_filter('wp_update_attachment_metadata', 'sacloudojs_thumb_upload');

        add_filter('wp_get_attachment_url', 'sacloudojs_object_storage_url');
        add_filter('wp_calculate_image_srcset', 'sacloudojs_calculate_image_srcset', 10, 5);

        if (Wp_Sacloud_Ojs\Options::$Instance->DeleteObject === '1') {
            add_action("sacloudojs_object_uploaded", "sacloudojs_delete_object_after_upload", 10, 4);
        }
    } else {
        add_action('admin_notices', 'sacloudojs_show_incomplete_setting_notice');
    }

    // Load WP-CLI command
    if (defined('WP_CLI') && WP_CLI) {
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wp-cli.php';
        \WP_CLI::add_command('sacloud-ojs', 'Sacloud_Ojs_WP_CLI_Command');
    }
}


function sacloudojs_show_incomplete_setting_notice()
{
    echo '<div class="message notice notice-error"><p>' . __('ObjectStorage settings is incompleted', 'wp-sacloud-ojs') . '<a href="options-general.php?page=wp-sacloud-ojs/wp-sacloud-ojs.php">[' . __("Settings", "wp-sacloud-ojs") . ']</a></p></div>';
}

function sacloudojs_deactivate()
{
    //Wp_Sacloud_Ojs\Options::deactivate();
}

function sacloudojs_uninstall()
{
    Wp_Sacloud_Ojs\Options::deactivate();
}


function sacloudojs_add_action_links($links)
{
    $add_link = '<a href="options-general.php?page=wp-sacloud-ojs/wp-sacloud-ojs.php">' . __("Settings", "wp-sacloud-ojs") . '</a>';
    array_unshift($links, $add_link);
    return $links;
}

function add_pages()
{
    $r = add_submenu_page('options-general.php', __("ObjectStorage", 'wp-sacloud-ojs'), __("ObjectStorage", 'wp-sacloud-ojs'), 'manage_options', __FILE__, 'option_page');
}

function option_page()
{
    wp_enqueue_script('sacloudojs-script', plugins_url('/script/sacloudojs.js', __FILE__), array('jquery'), '0.0.1', true);
    wp_enqueue_style('sacloudojs-style', plugins_url('style/sacloudojs.css', __FILE__));
    include "tpl/setting.php";
}

function sacloudojs_options()
{
    Wp_Sacloud_Ojs\Options::init();
}

// Connection test
function sacloudojs_connect_test()
{
    $accessKey = '';
    if (isset($_POST['accesskey'])) {
        $accessKey = sanitize_text_field($_POST['accesskey']);
    }

    $secret = '';
    if (isset($_POST['secret'])) {
        $secret = sanitize_text_field($_POST['secret']);
    }

    $bucket = '';
    if (isset($_POST['bucket'])) {
        $bucket = sanitize_text_field($_POST['bucket']);
    }

    $useSSL = '';
    if (isset($_POST['useSSL'])) {
        $useSSL = sanitize_text_field($_POST['useSSL']);
    }


    try {
        $ojs = __get_object_store_service($accessKey, $secret, $bucket, $useSSL, true);
        echo json_encode(array(
            'message' => __("Connection was Successfully.", "wp-sacloud-ojs"),
            'is_error' => false,
        ));
        exit;

    } catch (Exception $ex) {
        echo json_encode(array(
            'message' => __("Connection Error", 'wp-sacloud-ojs') . ":" . $ex->getMessage(),
            'is_error' => true,
        ));
        exit;
    }
}

function sacloudojs_client_auth($accessKey = null, $secret = null, $bucket = null, $useSSL = null, $force = false)
{
    Wp_Sacloud_Ojs\Options::load();
    try {
        $ojs = __get_object_store_service($accessKey, $secret, $bucket, $useSSL, $force);
        return true;
    } catch (Exception $ex) {
        return false;
    }

}

// Resync
function sacloudojs_resync()
{
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => null,
        'post_status' => null,
        'post_parent' => null,
        'orderby' => null,
        'order' => null,
        'exclude' => null,
    );

    $attachments = get_posts($args);
    if (!$attachments) {
        return array();
    }

    /**
     * Filter resync targets
     * @param array $attachments target images
     */
    $attachments = apply_filters('sacloudojs_resync_targets', $attachments);

    $dir = wp_upload_dir();

    $retval = array();
    foreach ($attachments as $attach) {
        $path = get_attached_file($attach->ID);
        $name = __generate_object_name_from_path($path);
        $metadata = wp_generate_attachment_metadata($attach->ID, $path);

        $upload_dir = dirname($path);
        $backup_metadatas = get_post_meta($attach->ID, "_wp_attachment_backup_sizes", false);


        if (empty($metadata) || is_wp_error($metadata)) {
            $retval[$name] = false;
            do_action('sacloudojs_resync_metadata_error', $attach, $metadata);
            continue;
        }

        do_action('sacloudojs_resync_upload', $attach);
        $retval[$name] = sacloudojs_upload_file($attach->ID);
        do_action('sacloudojs_resync_uploaded', $attach, $retval[$name]);
        if ($retval[$name]) {
            if (!empty($backup_metadatas)) {
                foreach ($backup_metadatas as $meta) {
                    foreach ($meta as $thumb) {
                        __upload_object($upload_dir . DIRECTORY_SEPARATOR . $thumb['file']);
                    }
                }
            }
            //regenerate thumbs.
            do_action('sacloudojs_resync_metadata_upload', $attach, $metadata);
            wp_update_attachment_metadata($attach->ID, $metadata);
            do_action('sacloudojs_resync_metadata_uploaded', $attach, $metadata);

        }
    }
    return $retval;
}

// Upload a media file.
function sacloudojs_upload_file($file_id)
{
    $path = get_attached_file($file_id);
    return __upload_object($path);
}

// Upload thumbnails
function sacloudojs_thumb_upload($metadatas)
{
    if (!isset($metadatas['sizes'])) {
        return $metadatas;
    }

    $dir = wp_upload_dir();
    foreach ($metadatas['sizes'] as $thumb) {
        $file = $dir['path'] . DIRECTORY_SEPARATOR . $thumb['file'];
        if (!__upload_object($file)) {
            throw new Exception("upload error");
        }
    }

    return $metadatas;
}

function sacloudojs_delete_file_with_thumb($metadatas, $file_id)
{
    $dir = wp_upload_dir();
    $base_file = $dir['basedir'] . DIRECTORY_SEPARATOR . $metadatas['file'];
    $upload_dir = dirname($base_file);
    $files = array($base_file);

    if (isset($metadatas['sizes'])) {
        foreach ($metadatas['sizes'] as $thumb) {
            $files[] = $upload_dir . DIRECTORY_SEPARATOR . $thumb['file'];
        }
    }

    $backup_metadatas = get_post_meta($file_id, "_wp_attachment_backup_sizes", false);
    if (!empty($backup_metadatas)) {
        foreach ($backup_metadatas as $meta) {
            foreach ($meta as $thumb) {
                $files[] = $upload_dir . DIRECTORY_SEPARATOR . $thumb['file'];
            }
        }
    }

    foreach ($files as $file) {
        wp_delete_file($file);
    }

    remove_filter('wp_update_attachment_metadata', 'sacloudojs_delete_file_with_thumb', 999999);
    return $metadatas;
}

// Delete an object by file_id
function sacloudojs_delete_object_by_id($file_id)
{

    $metadatas = wp_get_attachment_metadata($file_id);

    $dir = wp_upload_dir();
    $base_file = $dir['basedir'] . DIRECTORY_SEPARATOR . $metadatas['file'];
    $upload_dir = dirname($base_file);

    $files = array($base_file);

    if (isset($metadatas['sizes'])) {
        foreach ($metadatas['sizes'] as $thumb) {
            $files[] = $upload_dir . DIRECTORY_SEPARATOR . $thumb['file'];
        }
    }

    // add _wp_attachment_backup_sizes from wp_postmeta
    $backup_metadatas = get_post_meta($file_id, "_wp_attachment_backup_sizes", false);
    if (!empty($backup_metadatas)) {
        foreach ($backup_metadatas as $meta) {
            foreach ($meta as $thumb) {
                $files[] = $upload_dir . DIRECTORY_SEPARATOR . $thumb['file'];
            }
        }
    }

    foreach ($files as $file) {
        __delete_object($file);
    }
}

function sacloudojs_delete_object_after_upload($filepath, $object_name, $client, $result)
{
    add_filter('wp_update_attachment_metadata', 'sacloudojs_delete_file_with_thumb', 999999, 2);
}

function sacloud_after_image_edit($meta_id, $object_id, $meta_key, $meta_value)
{
    if ($meta_key === '_wp_attached_file') {
        $dir = wp_upload_dir();
        $file = $dir['basedir'] . DIRECTORY_SEPARATOR . $meta_value;
        return __upload_object($file);
    }
}


// Return object URL
function sacloudojs_object_storage_url($wpurl)
{

    $file_id = __get_attachment_id_from_url($wpurl);
    $path = get_attached_file($file_id);

    $object_name = __generate_object_name_from_path($path);

    return Wp_Sacloud_Ojs\Options::$Instance->getObjectURLByName($object_name);

}

// Return object URL(srcset)
function sacloudojs_calculate_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
{
    foreach ($sources as &$src) {
        $url = $src['url'];

        $object_name = __generate_object_name_from_url($url);
        if ($object_name) {
            $url = Wp_Sacloud_Ojs\Options::$Instance->getObjectURLByName($object_name);
            $src['url'] = $url;
        }
    }
    return $sources;
}

// -------------------- internal functions --------------------

// generate the object name from the filepath.
function __generate_object_name_from_path($path)
{

    $dir = wp_upload_dir();
    $name = basename($path);

    // strip left(wp_upload_dir:basedir)
    $prefix = str_replace($dir['basedir'] . DIRECTORY_SEPARATOR, '', $path);
    // string right(filename)
    $prefix = str_replace(DIRECTORY_SEPARATOR. $name, '', $prefix) . DIRECTORY_SEPARATOR;

    $container_name = Wp_Sacloud_Ojs\Options::$Instance->Container;
    if ($container_name != "") {
        $container_name .= "/";
    }

    return $container_name . $prefix . $name;
}

function __generate_object_name_from_url($url)
{

    $dir = wp_upload_dir();
    $pi = pathinfo($url);
    $name = $pi['basename'];

    // strip left(wp_upload_dir:baseurl)
    $prefix = str_replace($dir['baseurl'] . DIRECTORY_SEPARATOR, '', $url) ;
    // strip right(filename)
    $prefix = str_replace(DIRECTORY_SEPARATOR . $name, '', $prefix) . DIRECTORY_SEPARATOR;

    $container_name = Wp_Sacloud_Ojs\Options::$Instance->Container;
    if ($container_name != "") {
        $container_name .= "/";
    }

    return $container_name . $prefix . $name;

}


function __get_attachment_id_from_url($url)
{
    global $wpdb;

    $upload_dir = wp_upload_dir();
    if (strpos($url, $upload_dir['baseurl']) === false) {
        return null;
    }

    $url = str_replace($upload_dir['baseurl'] . '/', '', $url);

    $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $url));
    return $attachment_id;
}


function __upload_object($filepath)
{

    $bucket = Wp_Sacloud_Ojs\Options::$Instance->Bucket;
    // Get client
    $client = __get_object_store_service();
    $object_name = __generate_object_name_from_path($filepath);

    // Upload file
    if (is_readable($filepath)) {

        do_action("sacloudojs_object_upload", $filepath, $object_name, $client);
        try {
            $fp = fopen($filepath, 'r');
            $client->putObject(array(
                'Bucket' => $bucket,
                'Key' => $object_name,
                'Body' => $fp
            ));
        } catch (Exception $ex) {
            do_action("sacloudojs_object_uploaded", $filepath, $object_name, $client, false);
            return false;
        }
        do_action("sacloudojs_object_uploaded", $filepath, $object_name, $client, true);
    } else {
        do_action("sacloudojs_object_missing", $filepath, $object_name, $client, false);
        return true;
    }

    return true;
}

function __head_object($object_name)
{

    $bucket = Wp_Sacloud_Ojs\Options::$Instance->Bucket;
    // Get client
    $client = __get_object_store_service();

    try {
        $object = $client->headObject(array(
            'Bucket' => $bucket,
            'Key' => $object_name,
        ));
        return $object;

    } catch (Exception $ex) {
        return false;
    }
}

function __delete_object($filepath)
{
    $bucket = Wp_Sacloud_Ojs\Options::$Instance->Bucket;
    // Get client
    $client = __get_object_store_service();
    $object_name = __generate_object_name_from_path($filepath);

    try {
        $object = $client->deleteObject(array(
            'Bucket' => $bucket,
            'Key' => $object_name,
        ));
        return $object;

    } catch (Exception $ex) {
        return false;
    }
}


function __get_object_store_service($accessKey = null, $secret = null, $bucket = null, $useSSL = null, $force = false)
{
    static $client = null;

    if (!$client || $force) {
        if ($accessKey == null) {
            $accessKey = Wp_Sacloud_Ojs\Options::$Instance->AccessKey;
        }
        if ($secret == null) {
            $secret = Wp_Sacloud_Ojs\Options::$Instance->Secret;
        }
        if ($bucket == null) {
            $bucket = Wp_Sacloud_Ojs\Options::$Instance->Bucket;
        }
        if ($useSSL == null) {
            $useSSL = Wp_Sacloud_Ojs\Options::$Instance->UseSSL;
        }

        $client = S3Client::factory(array(
            'key' => $accessKey,
            'secret' => $secret,
            'base_url' => Wp_Sacloud_Ojs\Options::$Instance->getObjectStorageHostURL($useSSL)
        ));

        try {
            $client->headBucket(array(
                'Bucket' => $bucket
            ));
        } catch (Exception $ex) {
            Wp_Sacloud_Ojs\Options::load();
            throw $ex;
        }
        Wp_Sacloud_Ojs\Options::load();
    }
    return $client;
}
