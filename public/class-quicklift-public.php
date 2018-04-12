<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.1.0
 *
 * @package    QuickLift
 * @subpackage QuickLift/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    QuickLift
 * @subpackage QuickLift/public
 * @author     Your Name <email@example.com>
 */
class QuickLift_Public {

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

	private $page_profile;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $quicklift       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $quicklift, $version ) {
		$this->quicklift = $quicklift;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->quicklift, plugin_dir_url( __FILE__ ) . 'css/quicklift-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->quicklift, plugin_dir_url( __FILE__ ) . 'js/quicklift-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'liftjs', 'https://lift3assets.lift.acquia.com/stable/lift.js', array( 'jquery' ), $this->version, false );

    $this->getPageProfile();
    $options = get_option( 'quicklift_options' );

    $scriptData = array(
      'account_id' => $options['quicklift_account_id'],
      'site_id' => $options['quicklift_site_id'],
      'assets' => $options['quicklift_assets'],
      'decision_api' => $options['quicklift_decision_api'],
      'auth_endpoint' => $options['quicklift_auth_endpoint']
    );

    $scriptData = array_merge($scriptData,$this->page_profile);

    wp_localize_script($this->quicklift, 'quicklift', $scriptData);

	}

  public function getPageProfile()
  {
    //$postID = url_to_postid( $_SERVER['REQUEST_URI'] , '_wpg_def_keyword', true );
    $currentPost = get_post();

    $post_terms = wp_get_post_terms();

    $term_array = array();
    $term_list = '';
    if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ){
      foreach ( $post_terms as $term ) {
        $term_array[] = $term->name;
      }
      $term_list = implode(",", $term_array);
    }

    $this->page_profile = [
      'author'=>$currentPost->post_author,
      'engagement_score'=>'1',
      'page_type'=>$currentPost->post_type,
      'post_id'=>$currentPost->ID,
      'published_date'=>$currentPost->post_date,
      'content_title'=>$currentPost->post_title,
      'content_type'=>$currentPost->post_type,
      'content_section'=>"",
      'content_keywords'=>$term_list,
      'persona'=>""
    ];
    return $this->page_profile;
  }
}
