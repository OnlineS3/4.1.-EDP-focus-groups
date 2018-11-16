<?php
function toolset_assigned_message( $kind, $slug = null ) {
	global $post;
	switch ( $kind ) {
		case "content-template":
			if ( function_exists( 'is_wpv_content_template_assigned' ) ) :
				if ( ! is_wpv_content_template_assigned() && ! ( preg_match( '/\[wpv\-(view|post\-body)/', get_the_content() ) ) && current_user_can( 'manage_options' ) ) : ?>
					<div class="panel panel-default not-assigned collapse in">
						<div class="panel-heading">
							<?php _e( 'There is no Content Template assigned', 'toolset_starter' ); ?>
							<a href="#" data-toggle="collapse" data-target=".not-assigned" class="alignright small">
								<i class="fa fa-close"></i> <?php _e( "Dismiss", 'toolset_starter' ); ?>
							</a>
						</div>
						<div class="panel-body">
							<div class="not-assigned-body">
								<h4><?php _e( "Do you want this page to look different?", 'toolset_starter' ); ?> </h4>
								<a class="btn btn-lg btn-primary"
								   href="<?php echo admin_url( 'admin.php?page=view-templates' ); ?>"
								   title="<?php _e( "Content Templates", 'toolset_starter' ); ?>">
									<?php _e( "Assign a Content Template", 'toolset_starter' ); ?>
								</a>
							</div>
							<div class="not-assigned-helper">

								<a href="http://wp-types.com/documentation/user-guides/view-templates/" target="_blank"
								   title="<?php _e( "Designing WordPress Content with Content Templates", 'toolset_starter' ); ?>">
									<?php _e( 'Learn about Content Templates', 'toolset_starter' ); ?>
								</a>

							</div>
						</div>
						<div class="panel-footer panel-footer-sm text-center">
							<?php _e( "You can see this message because you are logged in as a user who can assign Content Templates. <br>Your visitors won't see this message.", 'toolset_starter' ); ?>
						</div>
					</div>
				<?php
				endif;
			endif;
			break;

		case "views-archive" :
			if ( function_exists( 'is_wpv_wp_archive_assigned' ) ) :
				if ( ! is_wpv_wp_archive_assigned() && current_user_can( 'manage_options' ) ) : ?>
					<div class="panel panel-default not-assigned collapse in">
						<div class="panel-heading">
							<?php _e( 'There is no WordPress Archive Template assigned', 'toolset_starter' ); ?>
							<a href="#" data-toggle="collapse" data-target=".not-assigned" class="alignright small">
								<i class="fa fa-close"></i> <?php _e( "Dismiss", 'toolset_starter' ); ?>
							</a>
						</div>
						<div class="panel-body">
							<div class="not-assigned-body">
								<h4><?php _e( "Do you want this page to look different?", 'toolset_starter' ); ?> </h4>
								<a class="btn btn-lg btn-primary"
								   href="<?php echo admin_url( 'admin.php?page=view-archives' ); ?>"
								   title="<?php _e( "WordPress Archive", 'toolset_starter' ); ?>">
									<?php _e( "Assign a WordPress Archive Template", 'toolset_starter' ); ?>
								</a>
							</div>
							<div class="not-assigned-helper">
								<a href="http://wp-types.com/documentation/user-guides/normal-vs-archive-views/"
								   target="_blank"
								   title="<?php _e( "Customize Archive Page with WordPress Archive Template", 'toolset_starter' ); ?>">
									<?php _e( 'Learn about WordPress Archive Templates', 'toolset_starter' ); ?>
								</a>
							</div>
						</div>
						<div class="panel-footer panel-footer-sm text-center">
							<?php _e( "You can see this message because you are logged in as a user who can assign WordPress Archive Templates. <br>Your visitors won't see this message.", 'toolset_starter' ); ?>
						</div>
					</div>
				<?php endif; //if user can
			endif;
			break;
	}
}

