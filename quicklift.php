<?php

require 'vendor/autoload.php';

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             0.1.0
 * @package           QuickLift
 *
 * @wordpress-plugin
 * Plugin Name:       QuickLift
 * Plugin URI:        https://docs.acquia.com/
 * Description:       Acquia Lift Personalization for Wordpress. Does Not Support Syndication.
 * Version:           0.1.0
 * Author:            Kaynen Heikkinen
 * Author URI:        http://acquia.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       quicklift
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'QUICKLIFT', '0.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-quicklift-activator.php
 */
function activate_quicklift() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-quicklift-activator.php';
  QuickLift_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-quicklift-deactivator.php
 */
function deactivate_quicklift() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-quicklift-deactivator.php';
  QuickLift_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_quicklift' );
register_deactivation_hook( __FILE__, 'deactivate_quicklift' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-quicklift.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_quicklift() {

  $plugin = new QuickLift();
  $plugin->run();

}
run_quicklift();
