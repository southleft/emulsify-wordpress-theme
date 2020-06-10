<?php

$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}
// SET FOR TIMBER - WILL LOOK IN EACH DIR BELOW
Timber::$dirname = array( 'templates', 'components' );
Timber::$autoescape = false;

// CUSTOM POST TYPES & TAXONOMIES
require_once( 'functions/content-types.php' );
require_once( 'functions/content-taxonomies.php' );

class StarterSite extends Timber\Site {
	/** Add timber support. */
	public function __construct() {
    add_filter( 'jetpack_development_mode', '__return_true' );
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

    add_filter('acf/location/rule_values/post_type', 'acf_location_rule_values_post');
    function acf_location_rule_values_post( $choices ) {
      unset($choices[ 'cptm' ]);
      unset($choices[ 'cptm_tax' ]);
      return $choices;
    }

    // ADDING NAMESPACES FOR UNIFYING PATTERN CALLS
    add_filter('timber/loader/loader', function($loader) {
      $loader->addPath(__DIR__ . "/components/01-atoms", "atoms");
      $loader->addPath(__DIR__ . "/components/02-molecules", "molecules");
      $loader->addPath(__DIR__ . "/components/03-organisms", "organisms");
      $loader->addPath(__DIR__ . "/components/04-templates", "templates");
      return $loader;
    });

     function p72_custom_menu_order( $menu_ord ) {
      if ( !$menu_ord ) return true;

      return array(
        'index.php', // Dashboard
        'admin.php?page=jetpack#/dashboard',
        'separator1', // First separator
        'edit.php?post_type=page', // Pages
        'edit.php', // Posts
        'edit.php?post_type=team', // Team
        'edit.php?post_type=portfolio', // Portfolio
        'edit.php?post_type=testimonial', // Testimonials
        'upload.php', // Media
        'edit-comments.php', // Comments
        
        'separator2', // Second separator
        'themes.php', // Appearance
        'plugins.php', // Plugins
        'users.php', // Users
        'tools.php', // Tools
        'options-general.php', // Settings
        'separator-last', // Last separator
      );
    }
    add_filter( 'custom_menu_order', 'p72_custom_menu_order', 10, 1 );
    add_filter( 'menu_order', 'p72_custom_menu_order', 10, 1 );

    add_theme_support( 'post-formats', array( 'audio', 'video' ) );

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
  // BEM HELPER
	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig\Extension\StringLoaderExtension() );
		$bem_helper = new \Twig\TwigFunction('bem', function ( $block = '',  $element, $modifiers = [], $extra = [], $attributes = '' ) {
      $classes = array();
      if ( $block ) {
        array_push( $classes, $block . '__' . $element );
        if ( is_array( $modifiers ) && count( $modifiers ) > 0 ) {
          foreach ( $modifiers as $modifier ) {
            array_push( $classes, $block . '__' . $element . '--' . $modifier );
          }
        }
      } else {
        array_push( $classes, $element );
        if ( is_array( $modifiers ) && count( $modifiers ) > 0 ) {
          foreach ( $modifiers as $modifier ) {
            array_push( $classes, $element . '--' . $modifier );
          }
        }
      }
      // EXTRA NON-BEM CLASSES
      if ( is_array( $extra ) && count( $extra ) > 0 ) {
        foreach ( $extra as $extra_class ) {
          array_push( $classes, $extra_class);
        }
      }
      $attributes = 'class="' . implode( ' ', $classes ) . '"';
      return $attributes;
    });
    $twig->addFunction($bem_helper);

    $add_attributes = new \Twig\TwigFunction( 'add_attributes', function ( $additional_attributes = [] ) {
      $attributes = [];
      foreach ( $additional_attributes as $key => $value ) {
        if (is_array($value)) {
          foreach ($value as $index => $item) {
            if (strpos($item, $key . '=') !== FALSE) {
              parse_str($item, $result);
              unset($value[$index]);
              $value[] = substr($result[$key], 1, -1);
            }
          }
          $attributes[] = $key . '="' . implode(' ', $value) . '"';
          } else {
          // Handle bem() output.
          if (strpos($value, $key . '=') !== FALSE) {
            $attributes[] = $value;
          }
          else {
            $attributes[] = $key . '="' . $value . '"';
          }
        }
      }
      return implode( ' ', $attributes );
    });
    $twig->addFunction( $add_attributes );
		return $twig;
	}

  // PORTING OVER DRUPAL'S TWIG FILTER: WITHOUT
  // PUTTING THIS HERE AS A STUB
  function twig_without($element) {
    $filtered_element = $element;
    $args = func_get_args();
    unset($args[0]);
    foreach ($args as $arg) {
      if (isset($filtered_element[$arg])) {
        unset($filtered_element[$arg]);
      }
    }
    return $filtered_element;
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

