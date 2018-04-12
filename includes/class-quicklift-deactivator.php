<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    QuickLift
 * @subpackage QuickLift/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    QuickLift
 * @subpackage QuickLift/includes
 * @author     Your Name <email@example.com>
 */
class QuickLift_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
    delete_option( 'quicklift_options' );
	}

}
