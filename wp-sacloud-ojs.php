<?php

/**
 * Plugin Name: SakuraCloud ObjectStorage Plugin
 * Plugin URI: https://github.com/yamamoto-febc/wp-sacloud-ojs
 * Description: SakuraCloud ObjectStorage Plugin is a simple plugin for WordPress that helps you to synchronizes media files with SakuraCloud Object Storage.
 * Author: Kazumichi Yamamoto
 * Author URI: https://github.com/yamamoto-febc
 * Text Domain: wp-sacloud-ojs
 * Version: 0.0.4
 * License: GPLv2
*/

// Text Domain
load_plugin_textdomain('wp-sacloud-ojs', false, basename(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'lang');

// Load SDKs
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// use OpenCloud\Openstack namespace
use Aws\S3\S3Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

function add_pages() {
    $r = add_submenu_page('options-general.php', __("SakuraCloud ObjectStorage" , 'wp-sacloud-ojs'), __("SakuraCloud ObjectStorage" , 'wp-sacloud-ojs'), 8, __FILE__, 'option_page');
}

function option_page() {
    wp_enqueue_script('sacloudojs-script', plugins_url( '/script/sacloudojs.js' , __FILE__ ), array( 'jquery' ), '0.0.1',true);
    wp_enqueue_style('sacloudojs-style', plugins_url('style/sacloudojs.css', __FILE__));

    // Default options
    $host = get_option('sacloudojs-endpoint-host');
    if ($host == null || $host == "") {
        update_option('sacloudojs-endpoint-host', 'b.sakurastorage.jp');
    }

    if (get_option('sacloudojs-delobject') == null) {
        update_option('sacloudojs-delobject', 1);
    }

    $messages = array();
    if(isset($_POST['resync']) && $_POST['resync']) {
        $files = sacloudojs_resync();
        foreach($files as $file => $stat) {
            if($stat === true) {
                $messages[] = "$file uploaded.";
            } else if($stat === false) {
                $messages[] = "$file upload failed.";
            } else {
                $messages[] = "$file skiped.";
            }
        }
    }
    include "tpl/setting.php";
}


function sacloudojs_options()
{
    register_setting('sacloudojs-options', 'sacloudojs-accesskey', 'strval');
    register_setting('sacloudojs-options', 'sacloudojs-secret'   , 'strval');
    register_setting('sacloudojs-options', 'sacloudojs-bucket'   , 'strval');
    register_setting('sacloudojs-options', 'sacloudojs-use-ssl', 'boolval');
    register_setting('sacloudojs-options', 'sacloudojs-endpoint-host'  , 'strval');

    // Use Cache URL
    register_setting('sacloudojs-options', 'sacloudojs-use-cache', 'boolval');


    // Container(Directory) name
    register_setting('sacloudojs-options', 'sacloudojs-container', 'strval');

    // Allow Extensions
    register_setting('sacloudojs-options', 'sacloudojs-extensions', 'strval');

    // Synchronization option.
    register_setting('sacloudojs-options', 'sacloudojs-delobject', 'boolval');

    register_setting('sacloudojs-resync', 'sacloudojs-resync', 'intval');
}

// Connection test
function sacloudojs_connect_test()
{
    $accessKey = '';
    if(isset($_POST['accesskey'])) {
        $accessKey = sanitize_text_field($_POST['accesskey']);
    }

    $secret = '';
    if(isset($_POST['secret'])) {
        $secret = sanitize_text_field($_POST['secret']);
    }

    $bucket = '';
    if(isset($_POST['bucket'])) {
        $bucket = sanitize_text_field($_POST['bucket']);
    }

    $useSSL = '';
    if(isset($_POST['useSSL'])) {
        $useSSL = sanitize_text_field($_POST['useSSL']);
    }


    try {
        $ojs = __get_object_store_service($accessKey , $secret , $bucket , $useSSL);
        echo json_encode(array(
                             'message' => "Connection was Successfully.",
                             'is_error' => false,
                     ));
        exit;

    } catch(Exception $ex) {
        echo json_encode(array(
                             'message' => "ERROR: ".$ex->getMessage(),
                             'is_error' => true,
                     ));
        exit;
    }
}

// Resync
function sacloudojs_resync() {
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
    if( ! $attachments) {
        return array();
    }

    $retval = array();
    foreach($attachments as $attach) {
        $path = get_attached_file($attach->ID);
        $name = __generate_object_name_from_path($path);
        $metadata = wp_generate_attachment_metadata( $attach->ID, $path );

        if ( empty( $metadata ) || is_wp_error( $metadata) ){
            $retval[$name] = false;
            continue;
        }

        $retval[$name] = sacloudojs_upload_file($attach->ID);
        if ($retval[$name]) {
            //regenerage thumbs.
            wp_update_attachment_metadata( $attach->ID, $metadata );
        }
    }
    return $retval;
}

// Upload a media file.
function sacloudojs_upload_file($file_id) {
    $path = get_attached_file($file_id);
    if( ! __file_has_upload_extensions($path)) {
        return null;
    }

    return __upload_object($path);
}

// Upload thumbnails
function sacloudojs_thumb_upload($metadatas) {
    if( ! isset($metadatas['sizes'])) {
        return $metadatas;
    }

    $dir = wp_upload_dir();
    foreach($metadatas['sizes'] as $thumb) {
        $file = $dir['path'] . DIRECTORY_SEPARATOR . $thumb['file'];
        if( ! __file_has_upload_extensions($file)) {
            return false;
        }

        if( ! __upload_object($file)) {
            throw new Exception("upload error");
        }
    }

    return $metadatas;
}

// Delete an object by file_id
function sacloudojs_delete_object_by_id($file_id){
    $path = get_attached_file($file_id);
    if( ! __file_has_upload_extensions($path)) {
        return true;
    }

    return __delete_object($path);
}

// Delete an object
function sacloudojs_delete_object($filepath) {
    if( ! __file_has_upload_extensions($filepath)) {
        return true;
    }
    __delete_object($filepath);
    return $filepath;

}


// Return object URL
function sacloudojs_object_storage_url($wpurl) {

    $file_id = __get_attachment_id_from_url($wpurl);
    $path = get_attached_file($file_id);

    if( ! __file_has_upload_extensions($path)) {
        return $wpurl;
    }

    $object_name = __generate_object_name_from_path($path);

    $bucket = get_option('sacloudojs-bucket');

    $useSSL = get_option('sacloudojs-use-ssl');
    $baseHost = get_option('sacloudojs-endpoint-host');
    $useCache = get_option('sacloudojs-use-cache');

    $pref = $useSSL == '1' ? "https://" : "http://";
    $host = $baseHost;
    if($useCache == '1') {
        $host = $bucket . ".c.sakurastorage.jp";
        $bucket = "";
    }

    if ($bucket != ""){
        $bucket .= "/";
    }

    $url = $pref . $host . '/' . $bucket .  $object_name;
    return $url;
}

// add date prefix to the filename.
function sacloudojs_modify_uploadfilename($file){
    $dir = wp_upload_dir();
    $prefix = str_replace($dir['basedir'] . DIRECTORY_SEPARATOR, '', $dir['path']);
    $prefix = str_replace(DIRECTORY_SEPARATOR, '-', $prefix);
    $file['name'] = $prefix . '-' . $file['name'];
    return $file;
}

// -------------------- WordPress hooks --------------------

add_action('admin_menu', 'add_pages');
add_action('admin_init', 'sacloudojs_options' );
add_action('wp_ajax_sacloudojs_connect_test', 'sacloudojs_connect_test');

add_action('add_attachment', 'sacloudojs_upload_file');
add_action('edit_attachment', 'sacloudojs_upload_file');
add_action('delete_attachment', 'sacloudojs_delete_object_by_id');
add_filter('wp_update_attachment_metadata', 'sacloudojs_thumb_upload');

if(get_option("sacloudojs-delobject") == 1) {
    add_filter('wp_delete_file', 'sacloudojs_delete_object');
}

add_filter('wp_handle_upload_prefilter', 'sacloudojs_modify_uploadfilename' );

add_filter('wp_get_attachment_url', 'sacloudojs_object_storage_url');


// -------------------- internal functions --------------------

// generate the object name from the filepath.
function __generate_object_name_from_path($path) {
    $container_name = get_option('sacloudojs-container');

    $dir = wp_upload_dir();
    $name = basename($path);
    $name = str_replace($dir['basedir'] . DIRECTORY_SEPARATOR, '', $name);
    $name = str_replace(DIRECTORY_SEPARATOR, '-', $name);
    $container_name = get_option('sacloudojs-container');
    if ($container_name != "" ){
        $container_name .= "/";
    }

    return $container_name . $name;
}

// Confirm the file extension that need uploads.
function __file_has_upload_extensions($file) {
    $extensions = get_option('sacloudojs-extensions');
    if($extensions == '') {
        return true;
    }

    $f = new SplFileInfo($file);
    if( ! $f->isFile()) {
        return false;
    }

    $fileext = $f->getExtension();
    $fileext = strtolower($fileext);

    foreach(explode(',', $extensions) as $ext) {
        if($fileext == strtolower($ext)) {
            return true;
        }
    }
    return false;
}

function __get_attachment_id_from_url($url) {
    global $wpdb;

    $upload_dir = wp_upload_dir();
    if(strpos($url, $upload_dir['baseurl']) === false){
        return null;
    }

    $url = str_replace($upload_dir['baseurl'] . '/', '', $url);

    $attachment_id = $wpdb->get_var($wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $url));
    return $attachment_id;
}


function __upload_object($filepath) {

    $bucket = get_option('sacloudojs-bucket');
    // Get client
    $client = __get_object_store_service();

    // Upload file
    if(is_readable($filepath)) {
        $fp = fopen($filepath, 'r');
        $object_name = __generate_object_name_from_path($filepath);
        $client->putObject(array(
            'Bucket' => $bucket,
            'Key' => $object_name,
            'Body' => $fp
        )) ;
    } else {
        return true;
    }

    return true;
}

function __head_object($object_name) {

    $bucket = get_option('sacloudojs-bucket');
    // Get client
    $client = __get_object_store_service();

    try {
        $object = $client->headObject(array(
            'Bucket' => $bucket,
            'Key' => $object_name,
        ));
        return $object;

    } catch(Exception $ex) {
        return false;
    }
}

function __delete_object($filepath) {
    $bucket = get_option('sacloudojs-bucket');
    // Get client
    $client = __get_object_store_service();
    $object_name = __generate_object_name_from_path($filepath);

    try {
        $object = $client->deleteObject(array(
            'Bucket' => $bucket,
            'Key' => $object_name,
        ));
        return $object;

    } catch(Exception $ex) {
        return false;
    }
}


function __get_object_store_service($accessKey = null ,$secret = null ,$bucket = null , $useSSL = null) {
    static $client = null;

    if( ! $client) {
        if($accessKey == null) {
            $accessKey = get_option('sacloudojs-accesskey');
        }
        if($secret == null) {
            $secret = get_option('sacloudojs-secret');
        }
        if($bucket == null) {
            $bucket = get_option('sacloudojs-bucket');
        }
        if($useSSL == null){
          $useSSL =  get_option('sacloudojs-use-ssl');
        }

        $baseHost = get_option('sacloudojs-endpoint-host');
        $pref = $useSSL == '1' ? "https://" : "http://";

        $client = S3Client::factory(array(
            'key'    => $accessKey,
            'secret' => $secret,
            'base_url'=> $pref . $baseHost
        ));

        $client->headBucket(array(
            'Bucket' => $bucket
        ));
    }
    return $client;
}
