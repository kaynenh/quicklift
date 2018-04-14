<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.1.0
 *
 * @package    QuickLift
 * @subpackage QuickLift/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    QuickLift
 * @subpackage QuickLift/admin
 * @author     Your Name <email@example.com>
 */
class QuickLift_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $quicklift    The ID of this plugin.
	 */
	private $quicklift;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $included_types;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $quicklift       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $quicklift, $version ) {

		$this->quicklift = $quicklift;
		$this->version = $version;
		$this->included_types = array("post", "page", "personalization");

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in QuickLift_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The QuickLift_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->quicklift, plugin_dir_url( __FILE__ ) . 'css/quicklift-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in QuickLift_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The QuickLift_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->quicklift, plugin_dir_url( __FILE__ ) . 'js/quicklift-admin.js', array( 'jquery' ), $this->version, false );

	}

  /**
   * Register the admin hooks
   *
   * @since    0.1.0
   */
  public function quicklift_admin() {
      add_action( 'admin_init', array($this, 'quicklift_settings_init') );
      add_options_page( 'QuickLift Settings', 'QuickLift', 'manage_options', 'quicklift-admin.php', array($this, 'lift_options_page'));
      add_options_page( 'QuickLift Status', 'QuickLift Status', 'manage_options', 'quicklift-status.php', array($this, 'lift_status_page'));

  }

  public function personalization_description( $views ){

    $screen = get_current_screen();
    $post_type = get_post_type_object($screen->post_type);

    if ($post_type->description) {
      printf('<h4>%s</h4>', esc_html($post_type->description)); // echo
    }

    return $views; // return original input unchanged
  }
  /**
   * Register the post publish hook
   *
   * @since    0.1.0
   */
  public function quicklift_post_publish($post_ID, $post) {

    $quickLift = new QuickLift_CH_Manager();

    if (in_array($post->post_type, $this->included_types)) {
      if ($quickLift->connected == true) {
        $existing_uuid = get_post_meta($post_ID, 'lift_uuid', true);
        $preview_image = get_the_post_thumbnail_url($post_ID, 'thumbnail');
        $preview_image = ($preview_image ? $preview_image : "undefined");

        $created = new DateTime($post->post_date_gmt);
        $modified = new DateTime($post->post_modified_gmt);
        $entity = $quickLift->quickLiftCreateEntity($existing_uuid, $post_ID, $post->post_type, $post->post_title, $created->format(DateTime::ATOM), $modified->format(DateTime::ATOM), $preview_image, $post->post_content);

        if ($existing_uuid != '') {
          $quickLift->quickLiftUpdateEntities($quickLift->entities);
        } else {
          $quickLift->quickLiftSaveEntities($quickLift->entities);
        }
      }
    }

    $this->exportWidget($quickLift);
  }

  /**
   * Register the post delete hook
   *
   * @since    0.1.0
   */
  public function quicklift_post_delete($post_ID) {

    $quickLift = new QuickLift_CH_Manager();

    if ($quickLift->connected == true) {
        $existing_uuid = get_post_meta($post_ID, 'lift_uuid', true);

        /*var_dump($existing_uuid);
        die();*/

        if ($existing_uuid != '') {
          $quickLift->quickLiftDeleteEntity($existing_uuid);
        }
    }

    $this->exportWidget($quickLift);
  }

  public function exportWidget($quickLift, $widget_name = 'WP_Widget_Recent_Posts') {
    //Recent Posts Widget
    if ($syndicateRecentPostsWidget = true) {
      ob_start();
      the_widget( $widget_name );
      $widget_html = ob_get_clean();
      //$widget_html = 'Recent Posts';
      $widget_stored_uuid = get_option('recent_posts_uuid', '');
      $widget = $quickLift->quickLiftCreateEntity($widget_stored_uuid, 100, 'widget', "Recent Posts", '', '', '', $widget_html);
      $widget_uuid = $widget->getUuid();
      add_option('recent_posts_uuid', $widget_uuid, '', 'yes');

      if ($widget_stored_uuid != '') {
        $quickLift->quickLiftUpdateEntities($quickLift->entities);
      } else {
        $quickLift->quickLiftSaveEntities($quickLift->entities);
      }
    }
  }

  /**
   * custom option and settings
   */
  function quicklift_settings_init() {
    // register a new section in the "quicklift" page
    register_setting( 'quicklift', 'quicklift_options' );
    add_settings_section(
      'quicklift_section_developers',
      __( 'Lift Settings', 'quicklift' ),
      array($this, 'quicklift_section_developers_cb'),
      'quicklift'
    );

    // register a new field in the "quicklift_section_developers" section, inside the "quicklift" page
    $settings = array(
      ['quicklift_account_id','Account ID'],
      ['quicklift_site_id','Site ID'],
      ['quicklift_assets','Assets URL'],
      ['quicklift_decision_api','Decision API'],
      ['quicklift_auth_endpoint','Auth Endpoint'],
      ['quicklift_ch_client_host','Client Host'],
      ['quicklift_ch_client_api','Client API'],
      ['quicklift_ch_client_secret','Client Secret'],
      ['quicklift_ch_client_name','Client Name']
    );
    foreach ($settings as $setting) {
      //register_setting( 'quicklift', 'qlo_'.$setting[0] );
      add_settings_field(
        $setting[0], // as of WP 4.6 this value is used only internally
        // use $args' label_for to populate the id inside the callback
        __($setting[1], 'quicklift'),
        array($this, 'quicklift_field_input_cb'),
        'quicklift',
        'quicklift_section_developers',
        [
          'label_for' => $setting[0],
          'class' => 'quicklift_row',
          'quicklift_custom_data' => 'custom_'.$setting[0],
        ]
      );
    }
  }

  function quicklift_section_developers_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter credentials below.', 'quicklift' ); ?></p>
    <?php
  }

  function quicklift_field_input_cb( $args ) {
    // get the value of the setting we've registered with register_setting()
    $options = get_option('quicklift_options');
    // output the field
    $field_name = isset( $args['label_for']) ? $args['label_for'] : ( '' );
    if (isset($options[$field_name])) {
      $value = $options[$field_name];
    } else {
      $value = '';
    }
    ?>
    <input type='text' id='<?php echo "quicklift_options[".$field_name."]" ?>' name='<?php echo "quicklift_options[".$field_name."]" ?>' value='<?php echo $value; ?>' />
    <?php
  }

  /**
   * Return the options page
   *
   * @since    0.1.0
   */
  public function lift_options_page() {

    ?>
    <div class="quicklift-wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <?php
      // check if the user have submitted the settings
      // wordpress will add the "settings-updated" $_GET parameter to the url
      if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'quicklift_messages', 'quicklift_message', __( 'Settings Saved', 'quicklift' ), 'updated' );
      }

      // show error/update messages
      //settings_errors( 'quicklift_messages' );
      ?>
      <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "quicklift"
        settings_fields( 'quicklift' );
        // output setting sections and their fields
        // (sections are registered for "quicklift", each field is registered to a specific section)
        do_settings_sections( 'quicklift' );
        // output save settings button
        submit_button( 'Save Settings' );
        ?>
      </form>
    </div>
    <?php
  }

  /**
   * Return the status page
   *
   * @since    0.1.0
   */
  public function lift_status_page() {

    echo "<h1>Lift Status</h1>";
    $quickLift = new QuickLift_CH_Manager();

    if($quickLift->connected == true) {
      //@todo SANITIZE THIS
      if (isset($_GET['d'])) {
          $quickLift->quickLiftDeleteEntity($_GET['d']);
      }
      $clients = $quickLift->quickLiftListClients();
      echo "<h3>Clients Registered for this Account</h3>";
      echo "<div style='background-color:white;padding: 10px;border:1px solid #999999;height:300px;overflow-y:scroll;'>";
      foreach ($clients as $client) {
        print_r($client);
        echo "<br /><br />";
      }
      echo "</div>";
      echo "<h3>Entities Tracked</h3>";
      echo "<div style='background-color:white;padding: 10px;border:1px solid #999999;height:300px;overflow-y:scroll;'>";
      $entities = $quickLift->quickLiftListEntities();
      echo "$entities";
      echo "</div>";
    } else {
      echo "Please connect to Lift to continue!";
    }
  }

  /**
   * Register a QuickLift Content Type
   *
   * @link http://codex.wordpress.org/Function_Reference/register_post_type
   */
  public function quicklift_content_init() {
    $labels = array(
      'name'               => _x( 'Personalizations', 'post type general name', 'quicklift-textdomain' ),
      'singular_name'      => _x( 'Personalization', 'post type singular name', 'quicklift-textdomain' ),
      'menu_name'          => _x( 'Personalizations', 'admin menu', 'quicklift-textdomain' ),
      'name_admin_bar'     => _x( 'Personalization', 'add new on admin bar', 'quicklift-textdomain' ),
      'add_new'            => _x( 'Add New', 'personalization', 'quicklift-textdomain' ),
      'add_new_item'       => __( 'Add New Personalization', 'quicklift-textdomain' ),
      'new_item'           => __( 'New Personalization', 'quicklift-textdomain' ),
      'edit_item'          => __( 'Edit Personalization', 'quicklift-textdomain' ),
      'view_item'          => __( 'View Personalization', 'quicklift-textdomain' ),
      'all_items'          => __( 'All Personalizations', 'quicklift-textdomain' ),
      'search_items'       => __( 'Search Personalizations', 'quicklift-textdomain' ),
      'parent_item_colon'  => __( 'Parent Personalizations:', 'quicklift-textdomain' ),
      'not_found'          => __( 'No personalizations found.', 'quicklift-textdomain' ),
      'not_found_in_trash' => __( 'No personalizations found in Trash.', 'quicklift-textdomain' )
    );

    $args = array(
      'labels'             => $labels,
      'description'        => __( 'Personalizations to use with Acquia Lift. Supports Full HTML only.', 'quicklift-textdomain' ),
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'personalization' ),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'supports'           => array( 'title', 'editor', 'thumbnail' ),
      'menu_icon'          => 'dashicons-admin-customizer'
    );

    register_post_type( 'personalization', $args );
  }

  function quicklift_widgets_init() {
    register_sidebar( array(
      'name' => __( 'Lift Only', 'quicklift' ),
      'id' => 'quicklift-1',
      'description' => __( 'Widgets in this area will only be added to Experience Builder.', 'quicklift' ),
      'before_widget' => '',
      'after_widget'  => '',
      'before_title'  => '',
      'after_title'   => '',
    ) );
  }

}
