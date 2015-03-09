<?php

// THIS IS A COMMENT WITH A TEMPLATE ENTRY Lex

/**
 * Plugin Name.
 *
* @package   Lex
* @author    gabrielstuff contact@soixantecircuits.fr
* @license   GPL-2.0+
* @link      http://soixantecircuits.fr
* @copyright 2014 gabrielstuff
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-lex-admin.php`
 *
 * @package Lex
 * @author  gabrielstuff contact@soixantecircuits.fr
 */
class Lex {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'lex';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		//add_filter( '@TODO', array( $this, 'filter_method_name' ) );

    if (get_option('auto-detection')){
      add_filter('the_content', array( $this, 'replace_glossaire_content') );
    }
		add_filter( 'single_template', array( $this, 'lexicon_template' ));

    add_action( 'wp_ajax_lexique_list_insert_dialog', 'lexique_list_insert_dialog' );

    function lexique_list_insert_dialog() {
      echo '<select id="lexique_list_dialog">';
      $loop = new WP_Query( array( 'post_type' => 'lex_word', 'posts_per_page' => -1, 'orderby' => "title", 'order'=>"asc") );
      while ( $loop->have_posts() ) : $loop->the_post();
        // Fix for capitalized words
        $title= sanitize_title(get_the_title()); // On met les premières lettres en majuscule
        $meta = get_post_meta(get_the_ID(), 'lex_word', true);
        $slug = (isset($meta['lex_slug']) && $meta['lex_slug'] != '')? $meta['lex_slug'] : $title;
        $simple_field = simple_fields_fieldgroup('lex_categorie');
        if (isset($simple_field['selected_value'])  && $simple_field['selected_option']['key'] !=  'dropdown_num_5'){
          $category = $simple_field['selected_value'];
        }
        else{
          $category = 'none';
        }
        $text = get_the_title() . ' (' . $category . ')';
        //$title = get_the_title();
          ?>
        <option class="" value="" data-id="<?php echo $slug?>"><?php echo $text;?></option>

        <?php
      endwhile;
      echo '</select>';
    }
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	 /**
	 * Register the plugin post type
	 *
	 * @since    1.0.0
	 */
	public function register_post_type() {

		$domain = $this->plugin_slug;
		register_post_type('lex_word',
	  array(  'label' => __('Lexicon', $domain),
	          'description' => __('The words of the glossary', $domain),
	          'public' => true,
	          'show_ui' => true,
	          'show_in_menu' => true,
	          'capability_type' => 'post',
	          'hierarchical' => true,
	          'rewrite' => false,
	          'query_var' => true,
	          'supports' => array('title','editor','author'),
	          'labels' => array (
	            'name' => __('Lexicon', $domain),
	            'singular_name' => __('Word', $domain),
	            'menu_name' => __('Lexicon', $domain),
	            'add_new' => __('Add a word', $domain),
	            'add_new_item' => __('Add a new word', $domain),
	            'edit' => __('Edit', $domain),
	            'edit_item' => __('Edit word', $domain),
	            'new_item' => __('New word', $domain),
	            'view' => __('View word', $domain),
	            'view_item' => __('View word', $domain),
	            'search_items' => __('Search lexicon', $domain),
	            'not_found' => __('No word found', $domain),
	            'not_found_in_trash' => __('No word found in Ttrash', $domain),
	            'parent' => __('Parent word', $domain)
	          )
	    )
	  );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	/*Allow to create Glossaire*/
function get_glossaire_link($word)
{
//  if(function_exists("icl_object_id"))
//    $permalink = get_permalink( icl_object_id( esc_attr( get_option('lexicon_page_id') ), 'page', true) );
//  else
//    $permalink = get_permalink(esc_attr( get_option('lexicon_page_id') ) );
//
//  return $permalink."?lettre=".substr($word, 0, 1)."#$word";
	$sanitizedWord =  sanitize_title($word);
	return "#lexique?word=$sanitizedWord";
}

function prepare_words($text, $word)
{
  $word_net = $word;
  $word = preg_quote($word,'/');

  // Quote the word with --
  $text = preg_replace("/\b(($word)s?)\b/iu", '--\1--', $text, 1);
  // Remove quotes if word is in a link <a>
  $text = preg_replace("/(<a[^>]*>[^<-]*)--([^<-]*)--/", '$1$2', $text);
//  $text = preg_replace("/(<a [^>]*\\bhref\\s*=\\s*\"\\K[^\"]*)--(hypomyelination)--([^\"]*)/ui", "$1$2$3", $text);

  // In case of double glossy. Ex: cellule & cellule souche
  $text = preg_replace("/----([a-zA-Z]*)--/i", "--$1", $text);

  // Remove quotes if word is in an attr. Ex: title="word"
  $text = preg_replace('/="([^"-]*)--([^"-]*)--/', '="$1$2', $text);

  // Apply the glossy
  //
  return $text;
}

function replace_glossaire_content($content)
{
	global $query_word;
	if (!isset($query_word)) {
		$query_word = new WP_Query(array('orderby' => 'title', 'order' => 'DESC', 'post_type' => 'lex_word', 'posts_per_page' => -1));
	}
	while ($query_word->have_posts()) : $query_word->the_post();
		$content = $this->prepare_words($content, get_the_title());
		$content = preg_replace("/--((" . preg_quote(get_the_title(), '/') . ")s?)--/i", '<a href="' . $this->get_glossaire_link(get_the_title()) . '" data-slug="' . sanitize_title(get_the_title()) . '" class="glossy" title="' . esc_attr(get_the_content()) . '" >\1</a>', $content);
	endwhile;
	wp_reset_query();
	return $content;
}


	static function lex_check_path($slug){
		$user_theme_template = "/plugins/lex/templates";

		if( file_exists( get_template_directory().$user_theme_template."/css/styles.css") ){
			wp_enqueue_style('user-css', get_template_directory_uri().$user_theme_template."/css/styles.css", array(), self::VERSION );
		} else {
			wp_enqueue_style( 'the-board-default-styles', plugins_url( 'templates/css/default.css', __FILE__ ), array(), self::VERSION );
		}

		if( file_exists( get_template_directory().$user_theme_template."/css/scripts.js") ){
			wp_enqueue_script('user-js', get_template_directory_uri().$user_theme_template."/css/scripts.js", array(  ), self::VERSION);
		} else {
			wp_enqueue_script( 'the-board-default-script', plugins_url( 'templates/js/default.js', __FILE__ ), array(  ), self::VERSION );
		}

		if( file_exists(get_template_directory().$user_theme_template."/".$slug.".php") ){
			return $path = get_template_directory().$user_theme_template."/".$slug.".php";
		} else {
			return $path = plugin_dir_path( __FILE__ ) . 'templates/'.$slug.'.php';
		}

		if( !isset($path) || !file_exists($path)){
			return __('No template found. Sorry.', LEX_PLUGIN_BASENAME);
		}
	}

	public function lexicon_template($single) {
		global $wp_query, $post;
		/* Checks for single template by post type */
		if ($post->post_type == "lex_word"){
			return Lex::lex_check_path('single_word');
		}
		return $single;
	}
}



