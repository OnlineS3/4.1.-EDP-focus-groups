<?php

if ( defined( 'WPDDL_VERSION' )) :
    get_header( 'layouts');
        the_ddlayout();
    get_footer( 'layouts' );
else:
    get_header();
	if ( have_posts()) : while ( have_posts() ) : the_post();
		toolset_assigned_message('content-template');
	    the_content();
        if ( comments_open( get_the_id() ) ) { ?>
            <?php if(get_theme_mod('ref_container_wrapper') == false) ?>
                <div class="container">
            <?php comments_template(); ?>
            <?php if(get_theme_mod('ref_container_wrapper') == false) ?>
                </div>
        <?php
        }
    endwhile; endif; // WP Loop
    get_footer();
endif; // IF Layouts are enabled
?>
