<?php
// Include custom nav walker
require_once('bs-nav-walker.php');
require_once('toolset-assigned-message.php');



// Support for Bootstrap Pager.
// More info: http://twitter.github.com/bootstrap/components.html#pagination
if ( ! function_exists('wpbootstrap_content_nav') ) {

	function wpbootstrap_content_nav() {
		global $wp_query;

		if ( $wp_query->max_num_pages > 1 ) : ?>

			<ul class="pagination">
				<li class="previous">
					<?php echo str_replace( '<a href', '<a rel="prev" href', get_next_posts_link( '&larr; ' . __( 'Olders posts', 'toolset_starter' ) ) ) ?>
				</li>
				<li class="next">
					<?php echo str_replace( '<a href', '<a rel="next" href', get_previous_posts_link( __( 'Newer posts', 'toolset_starter' ) . ' &rarr;' ) ) ?>
				</li>
			</ul>

		<?php endif;
	}

}


// Adds 'table' class for <table> tags. Bootstrap needs an additional 'table' class to style tables.
// More info: http://twitter.github.com/bootstrap/base-css.htm
if ( ! function_exists('wpbootstrap_add_table_class') ) {

	function wpbootstrap_add_table_class( $content ) {
		$table_has_class = preg_match( '/<table class="/', $content );	// FIXME: regex to skip additional elements between table and class

		if ( $table_has_class ) {
			$content = str_replace( '<table class="', '<table class="table ', $content );

		} else {
			$content = str_replace( '<table', '<table class="table"', $content );
		}
		return $content;
	}

	add_filter( 'the_content', 'wpbootstrap_add_table_class' );
	add_filter( 'comment_text', 'wpbootstrap_add_table_class' );
}


// Pagination function.
// Thanks to: https://gist.github.com/3774261
if ( ! function_exists('wpbootstrap_link_pages') ) {

	function wpbootstrap_link_pages( $args = '') {

		$defaults = array(
			'before'			=> '<ul class="pagination">',
			'after'				=> '</ul>',
			'next_or_number'	=> 'number',
			'nextpagelink'     => __( 'Next page', 'toolset_starter' ),
			'previouspagelink' => __( 'Previous page', 'toolset_starter' ),
			'pagelink'			=> '%',
			'echo'				=> 1
		);

		$r = wp_parse_args( $args, $defaults );
		$r = apply_filters( 'wp_link_pages_args', $r );
		extract( $r, EXTR_SKIP );

		global $page, $numpages, $multipage, $more, $pagenow;

		$output = '';
		if ( $multipage ) {
			if ( 'number' == $next_or_number ) {
				$output .= $before;
				for ( $i = 1; $i < ( $numpages + 1 ); $i = $i + 1 ) {
					$j = str_replace ('%', $i, $pagelink );
					$output .= ' ';
					if ( $i != $page || ( ( !$more ) && ( $page == 1 ) ) )
						$output .= '<li>' . _wp_link_page( $i );
					else
						$output .= '<li class="active"><a href="#">';

					$output .= $j;
					if ( $i != $page || ( ( !$more ) && ( $page == 1 ) ) )
						$output .= '</a>';
					else
						$output .= '</a></li>';
				}
				$output .= $after;
			} else {
				if ( $more ) {
					$output .= $before;
					$i = $page - 1;
					if ( $i && $more) {
						$output .= _wp_link_page( $i );
						$output .= $previouspagelink . '</a>';
					}
					$i = $page + 1;
					if ( $i <= $numpages && $more ) {
						$output .= _wp_link_page( $i );
						$output .= $nextpagelink . '</a>';
					}
					$output .= $after;
				}
			}
		}
		if ( $echo )
			echo $output;

		return $output;
	}
}



/** COMMENTS WALKER */
class Wpbootstrap_Comments extends Walker_Comment {

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;
		?>
		<ul class="children list-unstyled">
			<?php
		}

		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$GLOBALS['comment_depth'] = $depth + 1;
			?>
		</ul>
		</div>
		<?php
	}

	/** START_EL */
	function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		$GLOBALS['comment'] = $comment;
		global $post
		?>

		<li <?php comment_class();?> id="comment-<?php comment_ID(); ?>">
			<span class="comment-avatar <?php echo ( $comment->user_id === $post->post_author ? 'thumbnail' : '' ); ?>">
				<?php
				if ( $comment->user_id === $post->post_author) {
					echo get_avatar( $comment, 54 );
				} else {
					echo get_avatar( $comment, 64 );
				}
				?>
			</span>
			<div class="comment-body">
				<h4 class="comment-author vcard">
					<?php
					printf( '<cite>%1$s %2$s</cite>', get_comment_author_link(), ( $comment->user_id === $post->post_author ) ? '<span class="bypostauthor label label-primary"> ' . __( 'Post author', 'toolset_starter' ) . '</span>' : ''
					);
					?>
				</h4>
				<?php
				printf('<a href="%1$s"><time class="comment-date" datetime="%2$s">%3$s</time></a>',
						esc_url(get_comment_link( $comment->comment_ID )),
					get_comment_time( 'c' ), sprintf( '%1$s ' . __( 'at', 'toolset_starter' ) . ' %2$s', get_comment_date(), get_comment_time() )
				);
				?>

				<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="alert alert-info comment-awaiting-moderation">
						<?php _e( 'Your comment is awaiting moderation.', 'toolset_starter' ); ?>
					</p>
				<?php endif; ?>

				<div class="comment-content">
					<?php comment_text(); ?>
				</div>

				<div class="reply">
					<a class="btn btn-default btn-xs edit-link"
					   href="<?php echo get_edit_comment_link(); ?>"><?php _e( 'Edit', 'toolset_starter' ) ?></a>
					<?php
					comment_reply_link(array_merge( $args,
							array(
								'reply_text' => '<span class="btn btn-default btn-xs">' . __( 'Reply', 'toolset_starter' ) . '</span>',
								'after'      => '',
								'depth'      => $depth,
								'max_depth'	 => $args['max_depth'],
					)));
					?>
				</div>

				<?php if ( empty( $args['has_children']) ) : ?>
			</div>
			<?php endif; ?>

			<?php
		}

		function end_el( &$output, $comment, $depth = 0, $args = array() ) {
			?>
		</li>
		<?php
	}

}

// Changes the default comment form textarea markup
if ( ! function_exists('wpbootstrap_comment_form') ) {

	function wpbootstrap_comment_form( $defaults ) {
		$req = get_option('require_name_email');

		$defaults['comment_field'] = ''
				. '<div class="comment-form-comment form-group">'
		                             . '<label for="comment">' . __( 'Comment', 'toolset_starter' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label>'
					. '<textarea id="comment" class="form-control" name="comment" rows="8" aria-required="true"></textarea>'
				. '</div>';
		$defaults['comment_notes_after'] = ''
				. '<p class="form-allowed-tags help-block">'
		                                   . sprintf( __( 'You may use these', 'toolset_starter' ) . ' <abbr title="HyperText Markup Language">HTML</abbr> ' . __( 'tags and attributes:', 'toolset_starter' ) . '%s',
						'<pre>' . allowed_tags() . '</pre>')
				. '</p>';

		return $defaults;
	}

	add_filter( 'comment_form_defaults', 'wpbootstrap_comment_form' );
}

// Changes the default comment form fields markup
// Thanks to http://www.codecheese.com/2013/11/wordpress-comment-form-with-twitter-bootstrap-3-supports/
if ( ! function_exists('wpbootstrap_comment_form_fields') ) {

	function wpbootstrap_comment_form_fields( $defaults ) {

		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );
		$aria_req  = ( $req ? " aria-required='true'" : '' );
		$html5     = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;

		$defaults    =  array(
			'author' => '<div class="form-group comment-form-author">' . '<label for="author">' . __( 'Name', 'toolset_starter' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
						'<input class="form-control" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div>',
			'email'  => '<div class="form-group comment-form-email"><label for="email">' . __( 'Email', 'toolset_starter' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
						'<input class="form-control" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></div>',
			'url'    => '<div class="form-group comment-form-url"><label for="url">' . __( 'Website', 'toolset_starter' ) . '</label> ' .
						'<input class="form-control" id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></div>',
		);

		return $defaults;
	}

	add_filter( 'comment_form_default_fields', 'wpbootstrap_comment_form_fields' );
}


// Changes the default password protection form markup
if ( ! function_exists( 'wpbootstrap_password_form' ) ) {

	function wpbootstrap_password_form() {

		global $post;
		$label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
		$form = '<form class="protected-post-form form-inline" role="form" action="' . get_option( 'siteurl' ) . '/wp-login.php?action=postpass" method="post">'
				. '<div class="alert alert-info alert-dismissable">'
				. '<button type="button" class="close" data-dismiss="alert">&times;</button>'
		        . '<strong>' . __( 'This post is password protected.', 'toolset_starter' ) . '</strong> ' . __( "To view it please enter your password below", 'toolset_starter' )
				. '</div>'
				. '<div class="form-group">'
		        . '<label class="sr-only" for="' . $label . '">' . __( "Password: ", 'toolset_starter' ) . '</label>'
				. '<input type="password" class="form-control" placeholder="Password" name="post_password" id="' . $label . '" />'
				. '</div>'
		        . '<button type="submit" class="btn btn-primary"/>' . __( 'Submit', 'toolset_starter' ) . '</button>'
				. '</form>';

		return $form;
	}

	add_filter( 'the_password_form', 'wpbootstrap_password_form' );
} 

// removes invalid rel="category tag" attribute from the links
if ( ! function_exists('wpbootstrap_remove_category_rel') ) {

	function wpbootstrap_remove_category_rel( $link ) {
		$link = str_replace( 'rel="category tag"', "", $link );
		return $link;
	}

	add_filter( 'the_category', 'wpbootstrap_remove_category_rel' );
}


// Declare Bootstrap version the theme is built with
if( function_exists('ddlayout_set_framework'))
{
	ddlayout_set_framework('bootstrap-3');
}


/**
 EMERSON: Custom WPML Switcher for Toolset Starter
 Start
 */
$using_new_switcher	= false;
if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {	
	if (version_compare( ICL_SITEPRESS_VERSION , '3.6.0', '<')) {
		//WPML 3.6.0 and below
		$using_new_switcher	= false;
	} else {
		//WPML 3.6.0 and beyond
		$using_new_switcher	= true;
	}
}
if ( false === $using_new_switcher ) {
	/** Old switcher->Load the toolset starter WPML language switcher */
	add_filter( 'wp_nav_menu_items', 'toolset_starter_lang_switcher',10,2 );
	
	/** We want to remove the WPML default language switcher hook to load the customized lang switcher designed for this theme */
	global $icl_language_switcher,$sitepress_settings,$is_lang_selector_dropdown;
	if (is_object($icl_language_switcher)) {
		//Check selector type
		$is_lang_selector_dropdown=false;
		if (isset($sitepress_settings['icl_lang_sel_type'])) {
			$selector_type= $sitepress_settings['icl_lang_sel_type'];
			if ('dropdown' == $selector_type) {
				$is_lang_selector_dropdown=true;
			}
		}
		if ($is_lang_selector_dropdown) {	
			remove_filter('wp_nav_menu_items', array($icl_language_switcher, 'wp_nav_menu_items_filter'), 10, 2);
		}
	}
	
	/** Hook function */
	function toolset_starter_lang_switcher($items,$args) {	
		
		$multilingual=false;
		$languages='';
		
		//Check if we are on multilingual mode
		$languages= apply_filters( 'wpml_active_languages', NULL, array( 'skip_missing' => 0 ) );
		if ((!(empty($languages))) && (is_array($languages))) {
			//multilingual
			global $sitepress_settings;
			$multilingual=true;
		}
			
		//Let's retrieved languages
		if ($multilingual) {						
			if (empty($languages)) {
			   	//We have empty languages.
			   	//Test for backward compatibility with older WPML versions where active languages API is not yet defined
			   	if (function_exists('icl_get_languages')) {
			   		$languages = icl_get_languages('skip_missing=0');
			   	}
			}		  
			
			//Languages set, implies WPML is active
			global $is_lang_selector_dropdown;
			    	
			if(!empty($sitepress_settings['display_ls_in_menu']) && ( !function_exists( 'wpml_home_url_ls_hide_check' ) || !wpml_home_url_ls_hide_check() ) && ($is_lang_selector_dropdown) ){
			
			   	/** We want to display our customized lang switcher ONLY if
			   	/*  WPML -> Languages -> Language switcher options -> Language switcher in the WP Menu -> Display the language switcher in the WP Menu is checked
			    */
			    	 
			    /** TOOLSET STARTER 1.3.4: Add the customized lang switcher to the correct menu set in 'Display the language switcher in the WP Menu' */
			    /** START */
			    //Get the menu for WPML language switcher
			    $menu_match=false;
			    if (isset($sitepress_settings['menu_for_ls'])) {
			    	$menu_for_ls=$sitepress_settings['menu_for_ls'];
			    	$menu_for_ls=intval($menu_for_ls);
			    	if ($menu_for_ls > 0) {
			    		//menu defined, get the menu slug corresponding this ID
			    		$menu_details = get_term( $menu_for_ls, 'nav_menu' );
			    		$menu_for_ls_slug_setting = $menu_details->slug;
			    
			    		if (isset($args->menu->slug)) {
			    			$menu_slug_under_process=$args->menu->slug;
			    			if ($menu_slug_under_process == $menu_for_ls_slug_setting) {
			    				$menu_match=true;
			    			}
			    		}
			    	}
			    }
			    /** END */
			    			    		
			    if ((1 < count($languages)) && ($menu_match)){
			    	$ll_flag        = $languages[ICL_LANGUAGE_CODE]['country_flag_url'];		    			
			    	$ll_url         = $languages[ICL_LANGUAGE_CODE]['url'];
			    	$ll_code        = $languages[ICL_LANGUAGE_CODE]['language_code'];		    			
			    	$ll_nname       = $languages[ICL_LANGUAGE_CODE]['native_name'];	    			
			    	$ll_tname       = $languages[ICL_LANGUAGE_CODE]['translated_name'];
			   		//Flag content
			   		//Check if flags are enabled
			   		$flags_enabled=false;
			   		if (isset($sitepress_settings['icl_lso_flags'])) {
			   			$flag_setting=$sitepress_settings['icl_lso_flags'];
			   			if ($flag_setting) {
			   				$flags_enabled=true;
			   			} 
			   		}
			   			
			   		//Language name in display language
			   		$lang_name_disp_language_enabled=false;
			   		if (isset($sitepress_settings['icl_lso_display_lang'])) {
			   			$icl_lso_display_lang_setting=$sitepress_settings['icl_lso_display_lang'];
			   			if ($icl_lso_display_lang_setting) {
			   				$lang_name_disp_language_enabled=true;
			   			}
			   		}
		    		//Native language name
		    		$native_lang_name_enabled=false;
		    		if (isset($sitepress_settings['icl_lso_native_lang'])) {
		    			$icl_lso_native_lang_setting=$sitepress_settings['icl_lso_native_lang'];
		    			if ($icl_lso_native_lang_setting) {
		    				$native_lang_name_enabled=true;
		    			}
		    		}
		    					    			
		    		$flag_content_main='';	    			
		    		if ($flags_enabled) {
		    			$flag_content_main= '<img src="'.$ll_flag.'" height="12" alt="'.$ll_code.'" width="18" />';
		    					    				
		    		}
		    		$native_lang_main='';
		    		if ($native_lang_name_enabled) {
		    			$native_lang_main=$ll_nname;
		    		}
					$show_parenthesis=true;
					if (!($native_lang_name_enabled)) {
						//Native lang off, don't show parenthesis
						$show_parenthesis=false;
					}
						
					$open_parenthesis='';
					$close_parenthesis='';											
					if ($show_parenthesis) {
						$open_parenthesis='(';
						$close_parenthesis=')';
					}
						
					$lang_name_display_lang_main='';
					if ($lang_name_disp_language_enabled) {
						$lang_name_display_lang_main= $open_parenthesis.$ll_tname.$close_parenthesis;
					}
					if 	(($native_lang_name_enabled) && ($lang_name_disp_language_enabled)) {
						$lang_name_display_lang_main='';
					}					
		    		$items = $items.'<li class="dropdown lang"><a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="'.$ll_url.'">'.$flag_content_main.' '. $native_lang_main .' '.$lang_name_display_lang_main.'</a><ul class=dropdown-menu>';
		   			foreach($languages as $l){
		   				if(!$l['active']){
		  					$flag_content_loop='';
		   					$lang_name_disp_content='';
		   					$native_lang_disp_content='';
		   					if ($flags_enabled) {
		   						$flag_content_loop= '<img src="'.$l['country_flag_url'].'" height="12" alt="'.$l['language_code'].'" width="18" />';
		   					}
		   					if ($lang_name_disp_language_enabled) {
		   						$lang_name_disp_content=$open_parenthesis.$l['translated_name'].$close_parenthesis;
		   					}
		   					if ($native_lang_name_enabled) {
		   						$native_lang_disp_content=$l['native_name'];
		   					}
		   					$items = $items.'<li class="menu-item"><a href="'.$l['url'].'">'.$flag_content_loop.' '. $native_lang_disp_content .' '.$lang_name_disp_content.'</a></li>';
		   				}
		   			}
		   		}
		   	}  
		}	
		return $items;
	}
}
/** END */