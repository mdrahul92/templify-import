<?php
/**
 * Static functions used in the Templify Import Templates plugin.
 *
 * @package Templify Import Templates
 */



//use function request_filesystem_credentials;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class with static helper functions.
 */
class Helpers {
    /**
	 * Append content to the file.
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @param string $separator_text separates the existing content of the file with the new content.
	 * @return boolean|WP_Error, path to the saved file or WP_Error object with error message.
	 */
	public static function append_to_file( $content, $file_path, $separator_text = '' ) {
		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		// By this point, the $wp_filesystem global should be working, so let's use it to create a file.
		global $wp_filesystem;

		$existing_data = '';
		if ( file_exists( $file_path ) ) {
			$existing_data = $wp_filesystem->get_contents( $file_path );
		}

		// Style separator.
		$separator = PHP_EOL . '---' . $separator_text . '---' . PHP_EOL;

		if ( ! $wp_filesystem->put_contents( $file_path, $existing_data . $separator . $content . PHP_EOL ) ) {
			return new \WP_Error(
				'failed_writing_file_to_server',
				sprintf(
					__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'templify-import-templates' ),
					'<br>',
					$file_path
				)
			);
		}

		return true;
	}

    /**
	 * Get data from a file
	 *
	 * @param string $file_path file path where the content should be saved.
	 * @return string $data, content of the file or WP_Error object with error message.
	 */
	public static function data_from_file( $file_path ) {
		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		// By this point, the $wp_filesystem global should be working, so let's use it to read a file.
		global $wp_filesystem;

		$data = $wp_filesystem->get_contents( $file_path );

		if ( ! $data ) {
			return new \WP_Error(
				'failed_reading_file_from_server',
				sprintf(
					__( 'An error occurred while reading a file from your server! Tried reading file from path: %s%s.', 'templify-import-templates' ),
					'<br>',
					$file_path
				)
			);
		}

		// Return the file data.
		return $data;
	}


    /**
	 * Helper function: check for WP file-system credentials needed for reading and writing to a file.
	 *
	 * @return boolean|WP_Error
	 */
	private static function check_wp_filesystem_credentials() {
		// Check if the file-system method is 'direct', if not display an error.
		$file_system_method = apply_filters( 'templify-import-templates/file_system_method', 'direct' );
		if ( ! ( $file_system_method === get_filesystem_method() ) ) {
			return new \WP_Error(
				'no_direct_file_access',
				sprintf(
					__( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'templify-import-templates' ),
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
				)
			);
		}

		// Get plugin page settings.
		$plugin_page_setup = apply_filters( 'templify-import-templates/plugin_page_setup', array(
				'parent_slug' => 'themes.php',
				'page_title'  => esc_html__( 'One Click Demo Import' , 'templify-import-templates' ),
				'menu_title'  => esc_html__( 'Import Demo Data' , 'templify-import-templates' ),
				'capability'  => 'import',
				'menu_slug'   => 'pt-one-click-demo-import',
			)
		);

		// Get user credentials for WP file-system API.
		$demo_import_page_url = wp_nonce_url( $plugin_page_setup['parent_slug'] . '?page=' . $plugin_page_setup['menu_slug'], $plugin_page_setup['menu_slug'] );

		if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
			return new \WP_error(
				'filesystem_credentials_could_not_be_retrieved',
				__( 'An error occurred while retrieving reading/writing permissions to your server (could not retrieve WP filesystem credentials)!', 'templify-import-templates' )
			);
		}

		// Now we have credentials, try to get the wp_filesystem running.
		if ( ! WP_Filesystem( $creds ) ) {
			return new \WP_Error(
				'wrong_login_credentials',
				__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'templify-import-templates' )
			);
		}

		return true;
	}


	/**
	 * Process import file - this parses the widget data and returns it.
	 *
	 * @param string $file path to json file.
	 * @return object $data decoded JSON string
	 */
	private static function process_import_file( $file ) {
		// File exists?
		if ( ! file_exists( $file ) ) {
			return new \WP_Error(
				'form_import_file_not_found',
				__( 'Error: Form import file could not be found.', 'templify-import-templates' )
			);
		}
		$data = give_get_raw_data_from_file( $file, 1, 25, ',' );
		// // Get file contents and decode.
		// $data = Helpers::data_from_file( $file );

		// Return from this function if there was an error.
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return $data;
	}

	/**
	 * Import raw data forms.
	 *
	 * @param string $raw_data the data for the forms.
	 */
	public static function import_data( $raw_data ) {
		// Have valid data? If no data or could not decode.
		if ( empty( $raw_data ) ) {
			return new \WP_Error(
				'corrupted_give_import_data',
				__( 'Error: Give import data could not be read. Please try a different file.', 'templify-import-templates' )
			);
		}
		$import_setting = [];
		$raw_key = maybe_unserialize( 'a:29:{i:0;s:2:"id";i:1;s:0:"";i:2;s:6:"amount";i:3;s:8:"currency";i:4;s:0:"";i:5;s:11:"post_status";i:6;s:9:"post_date";i:7;s:9:"post_time";i:8;s:7:"gateway";i:9;s:4:"mode";i:10;s:7:"form_id";i:11;s:10:"form_title";i:12;s:10:"form_level";i:13;s:10:"form_level";i:14;s:12:"title_prefix";i:15;s:10:"first_name";i:16;s:9:"last_name";i:17;s:5:"email";i:18;s:12:"company_name";i:19;s:5:"line1";i:20;s:5:"line2";i:21;s:4:"city";i:22;s:5:"state";i:23;s:3:"zip";i:24;s:7:"country";i:25;s:0:"";i:26;s:7:"user_id";i:27;s:8:"donor_id";i:28;s:8:"donor_ip";}' );
		$import_setting['raw_key'] = $raw_key;
		$import_setting['dry_run'] = false;
		$main_key = maybe_unserialize( 'a:29:{i:0;s:11:"Donation ID";i:1;s:15:"Donation Number";i:2;s:14:"Donation Total";i:3;s:13:"Currency Code";i:4;s:15:"Currency Symbol";i:5;s:15:"Donation Status";i:6;s:13:"Donation Date";i:7;s:13:"Donation Time";i:8;s:15:"Payment Gateway";i:9;s:12:"Payment Mode";i:10;s:7:"Form ID";i:11;s:10:"Form Title";i:12;s:8:"Level ID";i:13;s:11:"Level Title";i:14;s:12:"Title Prefix";i:15;s:10:"First Name";i:16;s:9:"Last Name";i:17;s:13:"Email Address";i:18;s:12:"Company Name";i:19;s:9:"Address 1";i:20;s:9:"Address 2";i:21;s:4:"City";i:22;s:5:"State";i:23;s:3:"Zip";i:24;s:7:"Country";i:25;s:13:"Donor Comment";i:26;s:7:"User ID";i:27;s:8:"Donor ID";i:28;s:16:"Donor IP Address";}' );
		// Prevent normal emails.
		remove_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999 );
		remove_action( 'give_insert_user', 'give_new_user_notification', 10 );
		remove_action( 'give_insert_payment', 'give_payment_save_page_data' );
		$current_key = 1;
		foreach ( $raw_data as $row_data ) {
			$import_setting['donation_key'] = $current_key;
			give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
			$current_key ++;
		}

		// Check if function exists or not.
		if ( function_exists( 'give_payment_save_page_data' ) ) {
			add_action( 'give_insert_payment', 'give_payment_save_page_data' );
		}

		$results = array(
			'message' => __( 'Give data has been successfully imported.', 'templify-import-templates' ),
		);
		// Return results.
		return apply_filters( 'templify-import-templates/give_import_results', $results );
	}


}
