<?php
if( get_theme_mod( 'ref_color_styles', 1 ) != 1
    && ! array_key_exists( 'customize_ajax_ref_color_styles', $_REQUEST )
) {
	// abort; no color style defined and not an request by theme customizer
	return;
}


if( ! function_exists( 'toolset_starter_ajax_customizer_css' ) ) {
	// function for ajax response
	function toolset_starter_ajax_customizer_css( $custom_css = '', $custom_css_theme = '', $custom_css_woo ='' ) {
		if( ! array_key_exists( 'customize_ajax_ref_color_styles', $_REQUEST ) ) {
			// no ajax request
			return;
		}

		$custom_css_variations = array(
			'cacheCSS'          => $custom_css,
			'cacheCSSTheme'     => $custom_css . $custom_css_theme,
			'cacheCSSWoo'       => $custom_css . $custom_css_woo,
			'cacheCSSThemeWoo'  => $custom_css . $custom_css_theme . $custom_css_woo,
		);

		echo json_encode( $custom_css_variations );
		exit();
	}
}

if( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	// abort; php version lower than 5.3
	toolset_starter_ajax_customizer_css();
	return;
}

require get_template_directory() . "/functions/phpColor.php";

/**
 * Primary Color
 */
$primarycolor = isset( $_POST['customize_ajax_ref_color_styles'] )
	? $_POST['customize_ajax_ref_color_styles']
	: get_theme_mod( 'primary_color', '#3CBEFE' );

// prove color
if( ! preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $primarycolor ) ) {
	$primarycolor = '#3CBEFE';
}

/**
 * Theme CSS Active
 */
$theme_css = isset( $_POST['customize_ajax_ref_theme_styles'] )
	? intval( $_POST['customize_ajax_ref_theme_styles'] )
	: get_theme_mod( 'ref_theme_styles', 1 );

/**
 * Theme CSS Woo Active
 */
$theme_css_woo = isset( $_POST['customize_ajax_ref_wc_styles'] )
	? intval( $_POST['customize_ajax_ref_wc_styles'] )
	: get_theme_mod( 'ref_wc_styles', 1 );

try {
	// Even that we abort if php is running on < 5.3, the file gets initialized and would throw
	// a warning if we just use $color = new \Mexitek\PHPColors\Color
	$class_as_var_to_be_valid_for_php52_file_init = '\Mexitek\PHPColors\Color';
	$color = new $class_as_var_to_be_valid_for_php52_file_init( $primarycolor );
	$colorRGB = $color->getRgb();
} catch ( Exception $e ) {
	// error on \Mexitek\PHPColors\Color
	error_log( 'Error \Mexitek\PHPColors\Color: ' . $e->getMessage() );

	// for the user the color won't change but beside that everything else continues
	toolset_starter_ajax_customizer_css();
	return;
}

$custom_css_theme = $custom_css_woo = '';

$custom_css = '     
					/********************************
					*   Color
					**********************************/
					a,
					.text-primary,
					.pagination > li > a, .pagination > li > span,
					.btn-primary .badge,
					.btn-link,
					.list-group-item.active > .badge, .nav-pills > .active > a > .badge,
					.panel-primary > .panel-heading .badge,
					.wpt-taxonomy-popular-show-hide, .wpt-repadd
						{ color: '. $primarycolor  .'; }


					a:hover, a:focus,
					a.text-primary:hover,
					a.bg-primary:hover,
					.btn-link:hover, .btn-link:focus,
					.wpt-taxonomy-popular-show-hide:hover, .wpt-taxonomy-popular-show-hide:focus, .wpt-taxonomy-popular-show-hideactive, .wpt-repadd:hover, .wpt-repadd:focus, .wpt-repaddactive,
					.pagination > li > a:hover, .pagination > li > a:focus, .pagination > li > span:hover, .pagination > li > span:focus
						{ color: #'. $color->darken(20) .'; }


					/********************************
					*   Background
					**********************************/
					.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus,
					.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus,
					.progress-bar,
					.label-primary,
					.bg-primary,
					body .navbar-toggle, body .ddl-navbar-toggle,
					body .ddl-dropdown-menu > .active > a, body .ddl-dropdown-menu > .active > a:hover, body .ddl-dropdown-menu > .active > a:focus
						{ background-color: '. $primarycolor  .'; }


					@media (min-width: 768px) {
						.navbar-default, body .ddl-navbar-default
							{ background-color: '. $primarycolor  .';	}
					}


					@media (max-width: 767px) {
						body .navbar-collapse, body .ddl-navbar-collapse
							{ background-color: '. $primarycolor  .';	}
					}

					body .navbar-default .navbar-toggle:hover, body .ddl-navbar-default .ddl-navbar-toggle:hover,
					body .navbar-default .navbar-toggle:focus, body .ddl-navbar-default .ddl-navbar-toggle:focus,
					.label-primary[href]:hover, .label-primary[href]:focus
						{ background-color: #'. $color->darken(20) .'; }


					/********************************
					*   Border
					**********************************/
					.nav .open > a, .nav .open > a:hover, .nav .open > a:focus,
					.panel-primary,
					.border-primary,
					.bypostauthor .avatar
						{ border-color: '. $primarycolor  .'; }

					.panel-primary > .panel-heading + .panel-collapse > .panel-body {
						border-top-color: '. $primarycolor  .';
					}
					.panel-primary > .panel-footer + .panel-collapse > .panel-body
						{ border-bottom-color:	'. $primarycolor  .'; }


					/********************************
					*   Color Variations
					**********************************/

					.btn-primary,
					.btn-primary.disabled,
					input[type="submit"],
					.pagination > .active > a, .pagination > .active > a:hover, .pagination > .active > a:focus, .pagination > .active > span, .pagination > .active > span:hover, .pagination > .active > span:focus,
					.list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus,
					.panel-primary > .panel-heading,
					.pagination:not(ul) ul:not(.wpv_pagination_dots) .wpv_page_current span, .pagination:not(ul) ul:not(.wpv_pagination_dots) span.wpv-archive-pagination-link-current,
					.pagination-dots >.active-dot> a:hover, .pagination-dots >li>a:hover, .pagination-dots >.active-dot> a
					{
						background-color: '. $primarycolor  .';
						border-color: #'. $color->darken(6) .';
					}
					.btn-primary:hover, input[type="submit"]:hover, .btn-primary:active, input[type="submit"]:active, .btn-primary.active, input.active[type="submit"], .open>.btn-primary.dropdown-toggle, .open>input.dropdown-toggle[type="submit"], .btn-primary:focus, input[type="submit"]:focus, .btn-primary.focus, input.focus[type="submit"], .btn-primary:active:hover, input[type="submit"]:active:hover, .btn-primary:active:focus, input[type="submit"]:active:focus, .btn-primary:active.focus, input[type="submit"]:active.focus, .btn-primary.active:hover, input.active[type="submit"]:hover, .btn-primary.active:focus, input.active[type="submit"]:focus, .btn-primary.active.focus, input.active.focus[type="submit"], .open>.btn-primary.dropdown-toggle:hover, .open>input.dropdown-toggle[type="submit"]:hover, .open>.btn-primary.dropdown-toggle:focus, .open>input.dropdown-toggle[type="submit"]:focus, .open>.btn-primary.dropdown-toggle.focus, .open>input.dropdown-toggle.focus[type="submit"]
					{
						background-color:   #'. $color->darken(20) .';
						border-color: #'. $color->darken(26) . '
					}
				  ';

if( $theme_css === true || $theme_css === 1 || isset( $_POST['customize_ajax_ref_color_styles'] ) ) {
	$custom_css_theme = '

					/********************************
					*   Color
					**********************************/

					.product-box a:hover, .well-product:not(.premium) h2 a:hover, .row_information .row_value a:hover, .product-box a:focus, .well-product:not(.premium) h2 a:focus, .row_information .row_value a:focus,
					.featured-item:hover header .title,
					.list-header a:hover,
					.category-listing h3 a,
					.product-description > h3 .glyphicon, .product-description > h3 .fa, .row_information .row_label .glyphicon, .row_information .row_label .fa,
					.main-footer a:hover
						{ color: '. $primarycolor  .'; }


					/********************************
					*   Background
					**********************************/
					.filter-list .search-header,
					.cart-icon
						{ background-color: '. $primarycolor  .'; }

					.cart-icon:hover, .cart-icon:focus
						{ background-color: #'. $color->darken(20) .'; }

					@media (max-width: 767px) {
						.list-element .location:before
							{ background-color: '. $primarycolor  .';	}
					}

					/********************************
					*   Border
					**********************************/

					.filter-list .search-header:after{ 
						border-color:  transparent transparent transparent '. $primarycolor  .';
						}
						
						@media (max-width: 767px){
							.filter-list .search-header:after { 
								border-color: '. $primarycolor  .' transparent transparent transparent; 
							}
						}

					.sidebar .filter-list .search-header:after
						{ border-color: '. $primarycolor  .' transparent transparent transparent; }

				    /********************************
					*   Color Variations
					**********************************/
					.product-buttons .price
						{   background: rgba('.$colorRGB['R'].','.$colorRGB['G'].','.$colorRGB['B'].', .3);
							color: #'. $color->darken(30) .'; }
					' ;
}

if( $theme_css_woo === true || $theme_css_woo === 1  || isset( $_POST['customize_ajax_ref_color_styles'] ) ) {
	$custom_css_woo = '
					/********************************
					*   Color
					**********************************/
					.woocommerce input[type="submit"].button .badge, button[type="submit"].button .badge, input[type="submit"].button.alt .badge, .woocommerce a.button.alt .badge, .woocommerce button.button.alt .badge, .woocommerce input.button.alt .badge, input[type="submit"] .badge,
					.woocommerce .star-rating.wc_views_star_rating span:before, .woocommerce-page .star-rating.wc_views_star_rating span:before,
					.pagination-dots .active-dot a
					{ color: '. $primarycolor  .'; }

					/********************************
					*   Background
					**********************************/

					.woocommerce .chosen-container .chosen-results li.highlighted, .woocommerce-page .chosen-container .chosen-results li.highlighted,
                    .sale-primary .wcviews_onsale_wrap .onsale
						{ background-color: '. $primarycolor  .'; }
						

					/********************************
					*   Border
					**********************************/
					.wcviews_onsale_wrap:before
						{ border-right-color:	'. $primarycolor  .'; }


                    .sale-primary .wcviews_onsale_wrap .onsale:after
                        {border-top-color:'. $primarycolor  .';
                        border-bottom-color:'. $primarycolor  .'}

					/********************************
					*   Color Variations
					**********************************/

					.woocommerce input[type="submit"].button, button[type="submit"].button, input[type="submit"].button.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, input[type="submit"],
					.woocommerce input.disabled[type="submit"].button, button.disabled[type="submit"].button, input.disabled[type="submit"].button.alt, .woocommerce a.disabled.button.alt, .woocommerce button.disabled.button.alt, .woocommerce input.disabled.button.alt, input.disabled[type="submit"], .btn-primary.disabled:hover, button.disabled[type="submit"].button:hover, .woocommerce a.disabled.button.alt:hover, .woocommerce button.disabled.button.alt:hover, .woocommerce input.disabled.button.alt:hover, input.disabled[type="submit"]:hover, .btn-primary.disabled:focus, button.disabled[type="submit"].button:focus, .woocommerce a.disabled.button.alt:focus, .woocommerce button.disabled.button.alt:focus, .woocommerce input.disabled.button.alt:focus, input.disabled[type="submit"]:focus, .btn-primary.disabled.focus, button.disabled.focus[type="submit"].button, .woocommerce a.disabled.focus.button.alt, .woocommerce button.disabled.focus.button.alt, .woocommerce input.disabled.focus.button.alt, input.disabled.focus[type="submit"], .btn-primary.disabled:active, button.disabled[type="submit"].button:active, .woocommerce a.disabled.button.alt:active, .woocommerce button.disabled.button.alt:active, .woocommerce input.disabled.button.alt:active, input.disabled[type="submit"]:active, .btn-primary.disabled.active, button.disabled.active[type="submit"].button, .woocommerce a.disabled.active.button.alt, .woocommerce button.disabled.active.button.alt, .woocommerce input.disabled.active.button.alt, input.disabled.active[type="submit"], .btn-primary[disabled], .woocommerce input[disabled][type="submit"].button, button[disabled][type="submit"].button, input[disabled][type="submit"].button.alt, .woocommerce a[disabled].button.alt, .woocommerce button[disabled].button.alt, .woocommerce input[disabled].button.alt, input[disabled][type="submit"], .btn-primary[disabled]:hover, button[disabled][type="submit"].button:hover, .woocommerce a[disabled].button.alt:hover, .woocommerce button[disabled].button.alt:hover, .woocommerce input[disabled].button.alt:hover, input[disabled][type="submit"]:hover, .btn-primary[disabled]:focus, button[disabled][type="submit"].button:focus, .woocommerce a[disabled].button.alt:focus, .woocommerce button[disabled].button.alt:focus, .woocommerce input[disabled].button.alt:focus, input[disabled][type="submit"]:focus, .btn-primary[disabled].focus, button[disabled].focus[type="submit"].button, .woocommerce a[disabled].focus.button.alt, .woocommerce button[disabled].focus.button.alt, .woocommerce input[disabled].focus.button.alt, input[disabled].focus[type="submit"], .btn-primary[disabled]:active, button[disabled][type="submit"].button:active, .woocommerce a[disabled].button.alt:active, .woocommerce button[disabled].button.alt:active, .woocommerce input[disabled].button.alt:active, input[disabled][type="submit"]:active, .btn-primary[disabled].active, button[disabled].active[type="submit"].button, .woocommerce a[disabled].active.button.alt, .woocommerce button[disabled].active.button.alt, .woocommerce input[disabled].active.button.alt, input[disabled].active[type="submit"], fieldset[disabled] .btn-primary, fieldset[disabled] .woocommerce input[type="submit"].button, .woocommerce fieldset[disabled] input[type="submit"].button, fieldset[disabled] button[type="submit"].button, fieldset[disabled] input[type="submit"].button.alt, fieldset[disabled] .woocommerce a.button.alt, .woocommerce fieldset[disabled] a.button.alt, fieldset[disabled] .woocommerce button.button.alt, .woocommerce fieldset[disabled] button.button.alt, fieldset[disabled] .woocommerce input.button.alt, .woocommerce fieldset[disabled] input.button.alt, fieldset[disabled] input[type="submit"], fieldset[disabled] .btn-primary:hover, fieldset[disabled] button[type="submit"].button:hover, fieldset[disabled] .woocommerce a.button.alt:hover, .woocommerce fieldset[disabled] a.button.alt:hover, fieldset[disabled] .woocommerce button.button.alt:hover, .woocommerce fieldset[disabled] button.button.alt:hover, fieldset[disabled] .woocommerce input.button.alt:hover, .woocommerce fieldset[disabled] input.button.alt:hover, fieldset[disabled] input[type="submit"]:hover, fieldset[disabled] .btn-primary:focus, fieldset[disabled] button[type="submit"].button:focus, fieldset[disabled] .woocommerce a.button.alt:focus, .woocommerce fieldset[disabled] a.button.alt:focus, fieldset[disabled] .woocommerce button.button.alt:focus, .woocommerce fieldset[disabled] button.button.alt:focus, fieldset[disabled] .woocommerce input.button.alt:focus, .woocommerce fieldset[disabled] input.button.alt:focus, fieldset[disabled] input[type="submit"]:focus, fieldset[disabled] .btn-primary.focus, fieldset[disabled] button.focus[type="submit"].button, fieldset[disabled] .woocommerce a.focus.button.alt, .woocommerce fieldset[disabled] a.focus.button.alt, fieldset[disabled] .woocommerce button.focus.button.alt, .woocommerce fieldset[disabled] button.focus.button.alt, fieldset[disabled] .woocommerce input.focus.button.alt, .woocommerce fieldset[disabled] input.focus.button.alt, fieldset[disabled] input.focus[type="submit"], fieldset[disabled] .btn-primary:active, fieldset[disabled] button[type="submit"].button:active, fieldset[disabled] .woocommerce a.button.alt:active, .woocommerce fieldset[disabled] a.button.alt:active, fieldset[disabled] .woocommerce button.button.alt:active, .woocommerce fieldset[disabled] button.button.alt:active, fieldset[disabled] .woocommerce input.button.alt:active, .woocommerce fieldset[disabled] input.button.alt:active, fieldset[disabled] input[type="submit"]:active, fieldset[disabled] .btn-primary.active, fieldset[disabled] button.active[type="submit"].button, fieldset[disabled] .woocommerce a.active.button.alt, .woocommerce fieldset[disabled] a.active.button.alt, fieldset[disabled] .woocommerce button.active.button.alt, .woocommerce fieldset[disabled] button.active.button.alt, fieldset[disabled] .woocommerce input.active.button.alt, .woocommerce fieldset[disabled] input.active.button.alt, fieldset[disabled] input.active[type="submit"],
					.woocommerce .product-comparision a.button,	.woocommerce .product-box-button a.button
					{
						background-color: '. $primarycolor  .';
						border-color: #'. $color->darken(6) .';
					}
					.woocommerce input[type="submit"].button:hover, button[type="submit"].button:hover, input[type="submit"].button.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover, input[type="submit"]:hover, .btn-primary:focus, .woocommerce input[type="submit"].button:focus, button[type="submit"].button:focus, input[type="submit"].button.alt:focus, .woocommerce a.button.alt:focus, .woocommerce button.button.alt:focus, .woocommerce input.button.alt:focus, input[type="submit"]:focus, .btn-primary.focus, .woocommerce input.focus[type="submit"].button, button.focus[type="submit"].button, input.focus[type="submit"].button.alt, .woocommerce a.focus.button.alt, .woocommerce button.focus.button.alt, .woocommerce input.focus.button.alt, input.focus[type="submit"], .btn-primary:active, .woocommerce input[type="submit"].button:active, button[type="submit"].button:active, input[type="submit"].button.alt:active, .woocommerce a.button.alt:active, .woocommerce button.button.alt:active, .woocommerce input.button.alt:active, input[type="submit"]:active, .btn-primary.active, .woocommerce input.active[type="submit"].button, button.active[type="submit"].button, input.active[type="submit"].button.alt, .woocommerce a.active.button.alt, .woocommerce button.active.button.alt, .woocommerce input.active.button.alt, input.active[type="submit"], .open > .btn-primary.dropdown-toggle, .woocommerce .open > input.dropdown-toggle[type="submit"].button, .open > button.dropdown-toggle[type="submit"].button, .woocommerce .open > a.dropdown-toggle.button.alt, .woocommerce .open > button.dropdown-toggle.button.alt, .woocommerce .open > input.dropdown-toggle.button.alt, .open > input.dropdown-toggle[type="submit"],
					.woocommerce .product-box-button a.button:hover, .woocommerce .product-box-button a.button:focus, .woocommerce .product-comparision a.button:hover, .woocommerce .product-comparision a.button:focus,
					.pagination-dots .active-dot a:hover
					{
						background-color:   #'. $color->darken(20) .';
						border-color: #'. $color->darken(26) .'
					}
					div.single_variation_wrap .single_variation span.price
						{   background: rgba('.$colorRGB['R'].','.$colorRGB['G'].','.$colorRGB['B'].', .3);
							color: #'. $color->darken(30) .'; }

		' ;
}

// ajax response (theme customizer)
toolset_starter_ajax_customizer_css( $custom_css, $custom_css_theme, $custom_css_woo );

// theme front-end output

$custom_css .= $custom_css_theme . $custom_css_woo;
$custom_css .= get_theme_mod( 'setting_custom_css' );

// Remove comments
$custom_css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $custom_css);
// Remove space after colons
$custom_css = str_replace(': ', ':', $custom_css);
// Remove whitespace
$custom_css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $custom_css);
// Enable GZip encoding.
ob_start("ob_gzhandler");
// Enable caching
header('Cache-Control: public');
// Expire in one day
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
header("Content-type: text/css");

// Write everything out
echo $custom_css;
