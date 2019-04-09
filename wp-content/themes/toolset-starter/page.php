<?php
if ( defined( 'WPDDL_VERSION' ) ) :
	get_header( 'layouts');
		the_ddlayout();
	get_footer( 'layouts' );
else:
	get_header();
	if ( have_posts() ) : while ( have_posts() ) : the_post();
		toolset_assigned_message('content-template');
		the_content();
	endwhile; endif;  // WP Loop
	get_footer();
endif; // IF Layouts are enabled
?>