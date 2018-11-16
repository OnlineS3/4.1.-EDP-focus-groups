<?php
$menu_position = get_theme_mod( 'menu_position' );
$menu_unstyled = get_theme_mod( 'menu_unstyled' );
$menu_custom_class = get_theme_mod( 'menu_custom_class', '' );

if( $menu_unstyled == "" ) {
	switch( $menu_position ) {
		case 'banner-below':
			$menu_class = 'nav navbar-nav';
			$menu_container_class = 'navbar navbar-default toolset-menu-banner-below';
			break;
		case 'static-top':
			$menu_class = 'nav navbar-nav';
			$menu_container_class = 'navbar navbar-default navbar-static-top toolset-menu-static-top';
			break;
		case 'fixed-top':
			$menu_class = 'nav navbar-nav';
			$menu_container_class = 'navbar navbar-default navbar-fixed-top toolset-menu-fixed-top';
			break;
		case 'banner-inside':
		default:
			$menu_position = 'banner-inside';
			$menu_class = 'nav navbar-nav';
			$menu_container_class = 'nav-wrap navbar navbar-default toolset-menu-banner-inside';
			break;
	}
} else {
	$menu_class = '';
	$menu_container_class = '';
}

$menu_container_class .= ' toolset-menu-container ' . $menu_custom_class;

function toolset_starter_menu( $menu_position, $menu_class, $menu_container_class ) { ?>
<nav class="<?php echo $menu_container_class; ?>" role="navigation">
	<?php if( $menu_position === 'static-top' || $menu_position === 'fixed-top' )
		echo '<div class="container">'; ?>
	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		<span class="sr-only"><?php _e( 'Toggle navigation', 'toolset_starter' ); ?></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>
	<div class="collapse navbar-collapse" id="nav-main">
		<?php
		wp_nav_menu(array(
				'theme_location' => 'header-menu',
				'depth'          => 5,
				'menu_class'     => $menu_class,
				'fallback_cb'    => 'wpbootstrap_menu_fallback',
				'walker'         => new Wpbootstrap_Nav_Walker(),
		));
		?><!-- #nav-main -->
	</div><!-- .navbar-collapse -->
	<?php if( $menu_position === 'static-top' || $menu_position === 'fixed-top' )
		echo '</div>'; ?>
</nav><!-- .navbar -->
<?php }
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<link rel="profile" href="http://gmpg.org/xfn/11"/>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>"/>

	<?php
	if( is_admin_bar_showing() && ( $menu_position === 'static-top' || $menu_position === 'fixed-top' ) )
		remove_action('wp_head', '_admin_bar_bump_cb');
	do_action( 'wpbootstrap_before_wp_head' );
	wp_head();
	do_action( 'wpbootstrap_after_wp_head' );
	?>
	<?php
	if( ( $menu_position === 'static-top' && is_admin_bar_showing() ) || $menu_position === 'fixed-top' ) { ?>
		<style type="text/css" media="screen">
			<?php if( is_admin_bar_showing() ): ?>
			#wpadminbar{top:auto;bottom:0}@media screen and (max-width: 600px){#wpadminbar{position:fixed}}#wpadminbar .menupop .ab-sub-wrapper,#wpadminbar .shortlink-input{bottom:32px}@media screen and (max-width: 782px){#wpadminbar .menupop .ab-sub-wrapper,#wpadminbar .shortlink-input{bottom:46px}}@media screen and (min-width: 783px){.admin-bar.masthead-fixed .site-header{top:0}}
			<?php endif; ?>
			<?php if( $menu_position === 'fixed-top' ): ?>
			html{margin-top: 50px;}
			<?php endif; ?>
		</style>
	<?php }
	?></head>

<body <?php body_class(); ?>>
<?php if( $menu_position === 'static-top' || $menu_position === 'fixed-top' )
	toolset_starter_menu( $menu_position, $menu_class, $menu_container_class ); ?>
<div class="wrapper">
	<?php if( is_active_sidebar( 'sidebar-header' ) ): ?>
		<div class="container container-sidebar-header">
			<div class="header-top sidebar-header row">
				<?php dynamic_sidebar('sidebar-header'); ?>
			</div>
		</div>
	<?php endif;?>
	<?php if( has_header_image() ): ?>
		<div class="container-fluid header-background-image">
			<div class="row header-background-image">
				<img src="<?php header_image(); ?>" height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="" class="img-responsive"/>
			</div>
		</div>
	<?php endif; ?>
	<header class="container container-header">
		<div class="row js-header-height header-nav">
			<div class="col-sm-3 logo col-xs-6">
				<?php if(get_theme_mod( 'logo', get_template_directory_uri() . '/images/toolset-starter-logo.png') != '') :?>
					<a href="<?php echo esc_url( home_url() );?>">
						<img src="<?php echo get_theme_mod( 'logo', get_template_directory_uri() . '/images/toolset-starter-logo.png');?>" alt="">
					</a>
				<?php endif;?>
			</div>
			<div class="col-sm-9 static col-xs-5">
				<?php if( $menu_position === 'banner-inside' )
					toolset_starter_menu( $menu_position, $menu_class, $menu_container_class ); ?>
			</div>
		</div>
	</header>
	<section class="<?php echo (get_theme_mod('ref_container_wrapper', 1) == 1)? 'container' : '';?> container-main" role="main">

		<?php if( $menu_position === 'banner-below' )
			toolset_starter_menu( $menu_position, $menu_class, $menu_container_class ); ?>

