<?php
remove_theme_mod( 'menu_floating' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />	

	<?php
		do_action( 'wpbootstrap_before_wp_head' );
		wp_head();
		do_action( 'wpbootstrap_after_wp_head' );
	?>
</head>

<body <?php body_class(); ?>>