<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Sacloud_Ojs_WP_CLI_Command' ) ) {

    /**
     * Manage SakuraCloud-ObjectStorage
     */
	class Sacloud_Ojs_WP_CLI_Command extends WP_CLI_Command {

		/**
		 * Subcommand to upload all image from Server(wp-content/uploads)
		 *
		 * Examples:
		 * wp sacloud-ojs upload-all
		 *
		 * @subcommand upload-all
		 */
		public function upload_all( $args, $assoc_args ) {
            Wp_Sacloud_Ojs\Options::load();

            if (!sacloudojs_client_auth(null,null,null,null,true)){
                $message = __("API Token Authentication error", "wp-sacloud-ojs");
                WP_CLI::error( $message );
            }

            add_action("sacloudojs_object_uploaded" , array(get_called_class() , 'file_uploaded') , 10 , 4);
            add_action("sacloudojs_object_missing" , array(get_called_class() , 'file_uploaded') , 10 , 4);
            add_action("sacloudojs_resync_metadata_error" , array(get_called_class() , 'file_metadata_error')  , 10 , 2);

            sacloudojs_resync();
            $message = __( 'Uploaded all images to ObjectStorage.' , "wp-sacloud-ojs" );
            WP_CLI::success( $message );
		}

        /**
         * @subcommand
         */
		public static function file_uploaded($filepath , $object_name , $client , $result){
            WP_CLI::log(sprintf("%-50s : %s" , $object_name, $result ? "[success]" : "[error] file missing"));
        }

        /**
         * @subcommand
         */
        public static function file_metadata_error($attach , $metadata){
            $result = is_wp_error($metadata) ? "[wp_error] " . print_r($metadata, true) : "[error] metadata is empty";
            WP_CLI::log(sprintf("%-50s : %s" , $attach->post_title , $result));
        }
    }

}