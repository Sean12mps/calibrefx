<?php
/**
 * Calibrefx Mobile Helper
 *
 */

/**
 * calibrefx_get_mobile_template
 * this function will return the mobile folder from the child themes folder
 * this will use in add_filter 'template_directory'
 **/
function calibrefx_get_mobile_template( $template ) {
	if( FALSE === strrpos($template, CHILD_URI) ){
		$mobile_template = str_replace( CALIBREFX_URI, CHILD_MOBILE_URI, $template );
	} else {
		$mobile_template = str_replace( CHILD_URI, CHILD_MOBILE_URI, $template );
	}
	
	if ( file_exists( $mobile_template ) ) {
		return $mobile_template;
	} else {
		return $template;
	}
}

function calibrefx_mobile_scripts(){
	if( wp_is_mobile() ){
		wp_enqueue_style( 'jquery.mobile', CALIBREFX_MODULE_URL . '/minicfx/assets/css/jquery-mobile.min.css' );
		wp_enqueue_style( 'jquery.mobile', CALIBREFX_MODULE_URL . '/minicfx/assets/css/jquery.mobile.icons.css' );
		wp_enqueue_style( 'calibrefx.mobile', CALIBREFX_MODULE_URL . '/minicfx/assets/css/calibrefx-mobile.css' );
		wp_enqueue_script( 'function.mobile', CALIBREFX_MODULE_URL . '/minicfx/assets/js/function-mobile.js', array( 'jquery' )  );
		wp_enqueue_script( 'jquery.mobile', CALIBREFX_MODULE_URL . '/minicfx/assets/js/jquery-mobile.js', array( 'jquery' )  );

		wp_dequeue_style( 'jquery-superfish' );
		wp_dequeue_script( 'superfish' );

		if( file_exists( CHILD_MOBILE_URI . '/style.css' ) ){
			wp_enqueue_style( 'child.mobile', CHILD_MOBILE_URL . '/style.css' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'calibrefx_mobile_scripts' );

/**
 * If mobile site is enable and there is a mobile template, then display mobile layout on mobile
 */
function calibrefx_init_mobile_site() {
	global $calibrefx; 

	//Register mobile menu
	register_nav_menus( 
		array ( 'mobile' => apply_filters( 'mobile_menu_text', __( 'Mobile Navigation Menu', 'calibrefx' ) ) )
	);

	if( is_admin() OR ! wp_is_mobile() ) {
		return;
	}

	remove_theme_support( 'calibrefx-footer-widgets' );
   	remove_theme_support( 'calibrefx-header-right-widgets' );

	//Disable wp admin bar
	show_admin_bar(false);

	add_filter( 'body_class', 'calibrefx_mobile_site_body_class' );
	
	add_filter( 'wrapper_class', 'calibrefx_mobile_wrapper_class' );
	add_filter( 'wrapper_attr', 'calibrefx_mobile_wrapper_attr' );

	remove_action( 'calibrefx_after_header', 'calibrefx_do_nav' );
	remove_action( 'calibrefx_after_header', 'calibrefx_do_subnav', 15 );

	add_action( 'calibrefx_before_wrapper', 'calibrefx_do_top_mobile_nav' );
	add_action( 'calibrefx_after_wrapper', 'calibrefx_do_mobile_footer', 100 );

	add_filter( 'template_include', 'calibrefx_get_mobile_template' );

	if( file_exists( CHILD_MOBILE_URI . '/functions.php' ) ){
		load_template( CHILD_MOBILE_URI . '/functions.php' );
	}

	$calibrefx->hooks->remove( 'calibrefx_before_loop', 'calibrefx_do_breadcrumbs', 10 );
	$calibrefx->hooks->remove( 'calibrefx_before_loop', 'calibrefx_do_notification', 20 );
	$calibrefx->hooks->remove( 'calibrefx_sidebar', 'calibrefx_do_sidebar' );
	$calibrefx->hooks->remove( 'calibrefx_sidebar_alt', 'calibrefx_do_sidebar_alt' );
	$calibrefx->hooks->remove( 'calibrefx_after_post_content', 'calibrefx_do_author_box_single', 20 );
	$calibrefx->hooks->remove( 'calibrefx_footer', 'calibrefx_footer_area', 10 );

	//On mobile there is no sidebar
	add_filter( 'calibrefx_site_layout', 'calibrefx_layout_full_width' );
}
add_action( 'calibrefx_post_init', 'calibrefx_init_mobile_site', 15 );

/**
 * calibrefx_mobile_site_body_class
 * Add special body class on mobile view
 * 
 * @param  array $body_classes 
 * @return array
 */
function calibrefx_mobile_site_body_class( $body_classes ) {
	$body_classes[] = apply_filters( 'calibrefx_mobile_body_class', 'mobile-site' );

	return $body_classes;
}

function calibrefx_mobile_wrapper_class( $wrapper_class ){
	$wrapper_class .= 'ui-content ';

	return $wrapper_class;
}

function calibrefx_mobile_wrapper_attr( $wrapper_attr ){
	$wrapper_attr .= 'data-role="main" ';

	return $wrapper_attr;
}

/**
 * Show mobile menu navigation
 */
function calibrefx_do_top_mobile_nav() {
	?>
	<div data-role="header" data-position="fixed">
		<h1><?php the_title( ); ?></h1>
		<a href="#mobile-menu" data-icon="bullets" data-iconpos="notext" class="ui-btn-left"></a>
		<a href="#mobile-search" class="jqm-search-link ui-btn ui-btn-icon-notext ui-corner-all ui-icon-search ui-nodisc-icon ui-alt-icon ui-btn-right">Search</a>
	</div><!-- /header -->

	<div id="mobile-menu" class="mobile-menu" data-role="panel" data-position="left" data-position-fixed="true" data-display="overlay">
		<?php calibrefx_do_mobile_nav(); ?>
	</div>
	
	<div id="mobile-search" class="mobile-search" data-role="panel" data-position="right" data-position-fixed="true" data-display="overlay">
		<form class="ui-filterable">
			<input id="autocomplete-input" data-type="search" placeholder="Search...">
		</form>
		<ul id="autocomplete" data-role="listview" data-inset="true" data-filter="true" data-input="#autocomplete-input"></ul>		
	</div>



	<?php
}


/**
 * Show the mobile special menu 
 */
function calibrefx_do_mobile_nav() {
	global $calibrefx;

	$calibrefx->load->library( 'walker_nav_menu' );

	$nav = '';
	$args = '';

	$args = array(
		'theme_location' => 'mobile',
		'container' => '',
		'menu_class' => calibrefx_get_option( 'nav_fixed_top' ) ? 'navbar navbar-default navbar-fixed-top menu-primary menu ' : 'nav navbar-nav menu-primary menu ',
		'echo' => 0,
		'walker' => $calibrefx->walker_nav_menu,
	);

	$nav = wp_nav_menu( $args );

	$nav_class = apply_filters( 'nav_class', calibrefx_row_class() );

	$nav_output = sprintf( '
		<div id="mobile-nav" class="navbar navbar-default">
			 %1$s
		</div>
		<!-- end #mobile-nav -->', $nav );

	echo apply_filters( 'calibrefx_do_nav', $nav_output, $nav, $args );

}

function calibrefx_do_mobile_footer(){ ?>
	<div data-role="footer" data-position="fixed">
		<h5>Copyright &copy; <?php echo date('Y'); ?> <?php echo get_bloginfo( 'site_title' ); ?></h5>
	</div>
<?php
}

/**
 * calibrefx_mobile_themes_exist
 * Check if the mobile theme exist in Child themes
 **/
function calibrefx_mobile_themes_exist() {
	return file_exists( CHILD_MOBILE_URI );
}
