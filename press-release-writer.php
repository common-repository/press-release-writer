<?php

/**

Plugin Name: Press Release Writer 
Plugin URI: https://howtocreateapressrelease.com/press-release-writer 
Description: A press release writing tool for your WordPress website or blog. 
Version: 2.0 
Author: How To Create A Press Release 
Author URI: https://howtocreateapressrelease.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

A press release writing tool for your WordPress website or blog.  

**/

# Exit if accessed directly
if (!defined("ABSPATH"))
{
	exit;
}

# Constant

/**
 * Exec Mode
 **/
define("PRWIREPROWRITER_EXEC",true);

/**
 * Plugin Base File
 **/
define("PRWIREPROWRITER_PATH",dirname(__FILE__));

/**
 * Plugin Base Directory
 **/
define("PRWIREPROWRITER_DIR",basename(PRWIREPROWRITER_PATH));

/**
 * Plugin Base URL
 **/
define("PRWIREPROWRITER_URL",plugins_url("/",__FILE__));

/**
 * Plugin Version
 **/
define("PRWIREPROWRITER_VERSION","2.0"); 

/**
 * Debug Mode
 **/
define("PRWIREPROWRITER_DEBUG",false);  //change false for distribution



/**
 * Base Class Plugin
 * @author PR Wire Pro
 *
 * @access public
 * @version 2.0
 * @package Press Release Writer
 *
 **/

class PressReleaseWriter
{

	/**
	 * Instance of a class
	 * @access public
	 * @return void
	 **/

	function __construct()
	{
		add_action("plugins_loaded", array($this, "prwireprowriter_textdomain")); //load language/textdomain
		add_action("wp_enqueue_scripts",array($this,"prwireprowriter_enqueue_scripts")); //add js
		add_action("wp_enqueue_scripts",array($this,"prwireprowriter_enqueue_styles")); //add css
		add_action("init", array($this, "prwireprowriter_post_type_pressrelease_init")); // register a pressrelease post type.
		add_filter("the_content", array($this, "prwireprowriter_post_type_pressrelease_the_content")); // modif page for pressrelease
		add_action("after_setup_theme", array($this, "prwireprowriter_image_size")); // register image size.
		add_filter("image_size_names_choose", array($this, "prwireprowriter_image_sizes_choose")); // image size choose.
		add_action("init", array($this, "prwireprowriter_register_taxonomy")); // register register_taxonomy.
		add_action("wp_head",array($this,"prwireprowriter_dinamic_js"),1); //load dinamic js
		if(is_admin()){
			add_action("admin_enqueue_scripts",array($this,"prwireprowriter_admin_enqueue_scripts")); //add js for admin
			add_action("admin_enqueue_scripts",array($this,"prwireprowriter_admin_enqueue_styles")); //add css for admin
		}
	}


	/**
	 * Loads the plugin's translated strings
	 * @link http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_textdomain()
	{
		load_plugin_textdomain("press-release-writer", false, PRWIREPROWRITER_DIR . "/languages");
	}


	/**
	 * Insert javascripts for back-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_admin_enqueue_scripts($hooks)
	{
		if (function_exists("get_current_screen")) {
			$screen = get_current_screen();
		}else{
			$screen = $hooks;
		}
	}


	/**
	 * Insert javascripts for front-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_enqueue_scripts($hooks)
	{
			wp_enqueue_script("prwireprowriter_main", PRWIREPROWRITER_URL . "assets/js/prwireprowriter_main.js", array("jquery"),"2.0",true );
	}


	/**
	 * Insert CSS for back-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_register_style
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_admin_enqueue_styles($hooks)
	{
		if (function_exists("get_current_screen")) {
			$screen = get_current_screen();
		}else{
			$screen = $hooks;
		}
	}


	/**
	 * Insert CSS for front-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_register_style
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_enqueue_styles($hooks)
	{
		// register css
		wp_register_style("prwireprowriter_main", PRWIREPROWRITER_URL . "assets/css/prwireprowriter_main.css",array(),"2.0" );
			wp_enqueue_style("prwireprowriter_main");
	}


	/**
	 * Register custom post types (pressrelease)
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 * @access public
	 * @return void
	 **/

	public function prwireprowriter_post_type_pressrelease_init()
	{

		$labels = array(
			'name' => _x('Press Releases', 'post type general name', 'press-release-writer'),
			'singular_name' => _x('Press Release', 'post type singular name', 'press-release-writer'),
			'menu_name' => _x('Press Releases', 'admin menu', 'press-release-writer'),
			'name_admin_bar' => _x('Press Releases', 'add new on admin bar', 'press-release-writer'),
			'add_new' => _x('Add New', 'book', 'press-release-writer'),
			'add_new_item' => __('Add New Press Release', 'press-release-writer'),
			'new_item' => __('New Press Release', 'press-release-writer'),
			'edit_item' => __('Edit Press Release', 'press-release-writer'),
			'view_item' => __('View Press Release', 'press-release-writer'),
			'all_items' => __('All Press Releases ', 'press-release-writer'),
			'search_items' => __('Search Press Releases', 'press-release-writer'),
			'parent_item_colon' => __('Parent Press Releases', 'press-release-writer'),
			'not_found' => __('No Press Releases Found', 'press-release-writer'),
			'not_found_in_trash' => __('No Press Releases Found In Trash', 'press-release-writer'));

			$supports = array('title','editor','author','custom-fields','trackbacks','thumbnail','comments','revisions','post-formats','page-attributes');

			$args = array(
				'labels' => $labels,
				'description' => __('Press Releases', 'press-release-writer'),
				'public' => true,
				'menu_icon' => 'dashicons-editor-table',
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'pressrelease'),
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => null,
				'taxonomies' => array(), // array('category', 'post_tag','page-category'),
				'supports' => $supports);

			register_post_type('pressrelease', $args);


	}


	/**
	 * Retrieved data custom post-types (pressrelease)
	 *
	 * @access public
	 * @param mixed $content
	 * @return void
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content
	 **/

	public function prwireprowriter_post_type_pressrelease_the_content($content)
	{

		$new_content = $content ;
		if(is_singular("pressrelease")){
			if(file_exists(PRWIREPROWRITER_PATH . "/includes/post_type.pressrelease.inc.php")){
				require_once(PRWIREPROWRITER_PATH . "/includes/post_type.pressrelease.inc.php");
				$pressrelease_content = new Pressrelease_TheContent();
				$new_content = $pressrelease_content->Markup($content);
				wp_reset_postdata();
			}
		}

		return $new_content ;

	}


	/**
	 * Register a new image size.
	 * @link http://codex.wordpress.org/Function_Reference/add_image_size
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_image_size()
	{
	}


	/**
	 * Choose a image size.
	 * @access public
	 * @param mixed $sizes
	 * @return void
	 **/
	public function prwireprowriter_image_sizes_choose($sizes)
	{
		$custom_sizes = array(
		);
		return array_merge($sizes,$custom_sizes);
	}


	/**
	 * Register Taxonomies
	 * @https://codex.wordpress.org/Taxonomies
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_register_taxonomy()
	{
	}


	/**
	 * Insert Dinamic JS
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function prwireprowriter_dinamic_js($hooks)
	{
		_e("<script type=\"text/javascript\">");
		_e("</script>");
	}
}


new PressReleaseWriter();
