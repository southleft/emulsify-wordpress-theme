<?php

$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}
// SET FOR TIMBER - WILL LOOK IN EACH DIR BELOW
Timber::$dirname = array( 'templates', 'views' );
Timber::$autoescape = false;

// CUSTOM POST TYPES & TAXONOMIES
require_once( 'functions/content-types.php' );
require_once( 'functions/content-taxonomies.php' );

class StarterSite extends Timber\Site {
	/** Add timber support. */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		parent::__construct();
	}
	

  // YOU CAN ALSO ADD/SET IN EACH PHP TEMPLATE
	public function add_to_context( $context ) {
		$context['foo']   = 'bar';
		$context['stuff'] = 'I am a value set in your functions.php file';
		$context['notes'] = 'These values are available everytime you call Timber::context();';
		$context['menu']  = new Timber\Menu();
		$context['site']  = $this;
		return $context;
	}

	public function theme_supports() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
    add_theme_support( 'menus' );
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
	}

  // ADD NEW TWIG FUNCTIONALITY
	public function myfoo( $text ) {
		$text .= ' bar!';
		return $text;
	}

	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig\Extension\StringLoaderExtension() );
		$twig->addFilter( new Twig\TwigFilter( 'myfoo', array( $this, 'myfoo' ) ) );
		return $twig;
	}

  public function load_scripts() {
    $js_version = filemtime( get_template_directory() . '/dist/js/scripts.js' );  
    wp_enqueue_script( 'wp-scripts',  get_stylesheet_directory_uri() . '/dist/js/scripts.js', array(), $js_version, true );
  }
}

// IF TIMBER HAS NOT BEEN INSTALLED VIA COMPOSER - ALMOST ZERO CHANCE THIS HAPPENS
if ( ! class_exists( 'Timber' ) ) {
	add_action(
		'admin_notices',
		function() {
			echo '<div class="error"><p>Timber not installed</p></div>';
		}
	);

	add_filter(
		'template_include',
		function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);
	return;
}

new StarterSite();

