<?php
// Generated by ZF2's ./bin/classmap_generator.php
return array(
	'WPV_WPML_Shortcodes_Dialog' => dirname( __FILE__ ) . '/controllers/compatibility/wpml/wpv_wpml_shortcodes_dialog.php',
	'WPV_WPML_Shortcodes_Translation' => dirname( __FILE__ ) . '/controllers/compatibility/wpml/wpv_wpml_shortcodes_translation.php',
	'WPV_WPML_Integration' => dirname( __FILE__ ) . '/controllers/compatibility/wpml/wpv_wpml.php',
	'WPV_WPML_Integration_Embedded' => dirname( __FILE__ ) . '/controllers/compatibility/wpml/wpv_wpml_embedded.php',
	'WPV_Controller_Admin_Help' => dirname( __FILE__ ) . '/controllers/admin/help.php',
	'WPV_Page_Slug' => dirname( __FILE__ ) . '/controllers/page_slugs.php',
	'WPV_Output_Template_Repository' => dirname( __FILE__ ) . '/controllers/output_template_repository.php',
	'WPV_Main' => dirname( __FILE__ ) . '/controllers/main.php',
	// AJAX
	'WPV_Ajax'                                    => dirname( __FILE__ ) . '/controllers/ajax.php',
	'WPV_Ajax_Handler_Filter_Post_Relationship_Update' => dirname( __FILE__ ) . '/controllers/ajax/handler/filter_post_relationship_update.php',
	'WPV_Ajax_Handler_Filter_Post_Relationship_Delete' => dirname( __FILE__ ) . '/controllers/ajax/handler/filter_post_relationship_delete.php',
	'WPV_Ajax_Handler_Filter_Relationship_Action' => dirname( __FILE__ ) . '/controllers/ajax/handler/filter_relationship_action.php',
	'WPV_Ajax_Handler_Update_Content_Selection' => dirname( __FILE__ ) . '/controllers/ajax/handler/update_content_selection.php',
	'WPV_Ajax_Handler_Get_Relationships_Data' => dirname( __FILE__ ) . '/controllers/ajax/handler/get_relationships_data.php',
	// API
	'WPV_Api' => dirname( __FILE__ ) . '/controllers/api.php',
	'WPV_API_Legacy' => WPV_PATH . '/inc/api/hooks-api.php',
	'WPV_Api_Handler_Interface' => dirname( __FILE__ ) . '/controllers/api/handler/interface.php',
	'WPV_Api_Handler_Get_Available_Views' => dirname( __FILE__ ) . '/controllers/api/handler/get_available_views.php',
	'WPV_Api_Handler_Get_Available_Content_Templates' => dirname( __FILE__ ) . '/controllers/api/handler/get_available_content_templates.php',
	// Shortcodes
	'WPV_Shortcodes'                             => dirname( __FILE__ ) . '/controllers/shortcodes.php',
	'WPV_Shortcodes_GUI'                         => dirname( __FILE__ ) . '/controllers/shortcodes_gui.php',
	'WPV_Exception_Invalid_Shortcode_Attr_Item'  => dirname( __FILE__ ) . '/models/shortcode/exceptions.php',
	'WPV_Shortcode_Factory'                      => dirname( __FILE__ ) . '/models/shortcode/factory.php',
	'WPV_Shortcode_Interface'                    => dirname( __FILE__ ) . '/models/shortcode/interface.php',
	'WPV_Shortcode_Interface_Conditional'        => dirname( __FILE__ ) . '/models/shortcode/interface_conditional.php',
	'WPV_Shortcode_Interface_View'               => dirname( __FILE__ ) . '/models/shortcode/interface_view.php',
	'WPV_Shortcode_Base_View'                    => dirname( __FILE__ ) . '/models/shortcode/base_view.php',
	'WPV_Shortcode_Interface_GUI'                => dirname( __FILE__ ) . '/models/shortcode/interface_gui.php',
	'WPV_Shortcode_Base_GUI'                     => dirname( __FILE__ ) . '/models/shortcode/base_gui.php',
	'WPV_Shortcode_Empty'                        => dirname( __FILE__ ) . '/models/shortcode/empty.php',
	'WPV_Shortcode_Post_Link'                    => dirname( __FILE__ ) . '/models/shortcode/post/link.php',
	'WPV_Shortcode_Post_Link_GUI'                => dirname( __FILE__ ) . '/models/shortcode/post/link_gui.php',
	'WPV_Shortcode_Post_Title'                   => dirname( __FILE__ ) . '/models/shortcode/post/title.php',
	'WPV_Shortcode_Post_Title_GUI'               => dirname( __FILE__ ) . '/models/shortcode/post/title_gui.php',
	'WPV_Shortcode_Post_Url'                     => dirname( __FILE__ ) . '/models/shortcode/post/url.php',
	'WPV_Shortcode_Post_Url_GUI'                 => dirname( __FILE__ ) . '/models/shortcode/post/url_gui.php',
	'WPV_Shortcode_Post_Body'                    => dirname( __FILE__ ) . '/models/shortcode/post/body.php',
	'WPV_Shortcode_Post_Body_GUI'                => dirname( __FILE__ ) . '/models/shortcode/post/body_gui.php',
	'WPV_Shortcode_Post_Excerpt'                 => dirname( __FILE__ ) . '/models/shortcode/post/excerpt.php',
	'WPV_Shortcode_Post_Excerpt_GUI'             => dirname( __FILE__ ) . '/models/shortcode/post/excerpt_gui.php',
	'WPV_Shortcode_Post_Date'                    => dirname( __FILE__ ) . '/models/shortcode/post/date.php',
	'WPV_Shortcode_Post_Date_GUI'                => dirname( __FILE__ ) . '/models/shortcode/post/date_gui.php',
	'WPV_Shortcode_Post_Author'                  => dirname( __FILE__ ) . '/models/shortcode/post/author.php',
	'WPV_Shortcode_Post_Author_GUI'              => dirname( __FILE__ ) . '/models/shortcode/post/author_gui.php',
	'WPV_Shortcode_Post_Featured_Image'          => dirname( __FILE__ ) . '/models/shortcode/post/featured_image.php',
	'WPV_Shortcode_Post_Featured_Image_GUI'      => dirname( __FILE__ ) . '/models/shortcode/post/featured_image_gui.php',
	'WPV_Shortcode_Post_Id'                      => dirname( __FILE__ ) . '/models/shortcode/post/id.php',
	'WPV_Shortcode_Post_Id_GUI'                  => dirname( __FILE__ ) . '/models/shortcode/post/id_gui.php',
	'WPV_Shortcode_Post_Slug'                    => dirname( __FILE__ ) . '/models/shortcode/post/slug.php',
	'WPV_Shortcode_Post_Slug_GUI'                => dirname( __FILE__ ) . '/models/shortcode/post/slug_gui.php',
	'WPV_Shortcode_Post_Type'                    => dirname( __FILE__ ) . '/models/shortcode/post/type.php',
	'WPV_Shortcode_Post_Type_GUI'                => dirname( __FILE__ ) . '/models/shortcode/post/type_gui.php',
	'WPV_Shortcode_Post_Format'                  => dirname( __FILE__ ) . '/models/shortcode/post/format.php',
	'WPV_Shortcode_Post_Format_GUI'              => dirname( __FILE__ ) . '/models/shortcode/post/format_gui.php',
	'WPV_Shortcode_Post_Status'                  => dirname( __FILE__ ) . '/models/shortcode/post/status.php',
	'WPV_Shortcode_Post_Status_GUI'              => dirname( __FILE__ ) . '/models/shortcode/post/status_gui.php',
	'WPV_Shortcode_Post_Comments_Number'         => dirname( __FILE__ ) . '/models/shortcode/post/comments_number.php',
	'WPV_Shortcode_Post_Comments_Number_GUI'     => dirname( __FILE__ ) . '/models/shortcode/post/comments_number_gui.php',
	'WPV_Shortcode_Post_Class'                   => dirname( __FILE__ ) . '/models/shortcode/post/class.php',
	'WPV_Shortcode_Post_Class_GUI'               => dirname( __FILE__ ) . '/models/shortcode/post/class_gui.php',
	'WPV_Shortcode_Post_Edit_Link'               => dirname( __FILE__ ) . '/models/shortcode/post/edit_link.php',
	'WPV_Shortcode_Post_Edit_Link_GUI'           => dirname( __FILE__ ) . '/models/shortcode/post/edit_link_gui.php',
	'WPV_Shortcode_Post_Menu_Order'              => dirname( __FILE__ ) . '/models/shortcode/post/menu_order.php',
	'WPV_Shortcode_Post_Menu_Order_GUI'          => dirname( __FILE__ ) . '/models/shortcode/post/menu_order_gui.php',
	'WPV_Shortcode_Post_Field'                   => dirname( __FILE__ ) . '/models/shortcode/post/field.php',
	'WPV_Shortcode_Post_Field_GUI'               => dirname( __FILE__ ) . '/models/shortcode/post/field_gui.php',
	'WPV_Shortcode_Post_Field_Iterator'          => dirname( __FILE__ ) . '/models/shortcode/post/field_iterator.php',
	'WPV_Shortcode_Post_Field_Iterator_GUI'      => dirname( __FILE__ ) . '/models/shortcode/post/field_iterator_gui.php',
	'WPV_Shortcode_Post_Next_Link'               => dirname( __FILE__ ) . '/models/shortcode/post/next_link.php',
	'WPV_Shortcode_Post_Next_Link_GUI'           => dirname( __FILE__ ) . '/models/shortcode/post/next_link_gui.php',
	'WPV_Shortcode_Post_Previous_Link'           => dirname( __FILE__ ) . '/models/shortcode/post/previous_link.php',
	'WPV_Shortcode_Post_Previous_Link_GUI'       => dirname( __FILE__ ) . '/models/shortcode/post/previous_link_gui.php',
	'WPV_Shortcode_Post_Taxonomy'                => dirname( __FILE__ ) . '/models/shortcode/post/taxonomy.php',
	'WPV_Shortcode_Post_Taxonomy_GUI'            => dirname( __FILE__ ) . '/models/shortcode/post/taxonomy_gui.php',
	
	'WPV_Shortcode_WPML_Conditional'             => dirname( __FILE__ ) . '/models/shortcode/wpml/conditional.php',
	'WPV_Shortcode_WPML_Conditional_GUI'         => dirname( __FILE__ ) . '/models/shortcode/wpml/conditional_gui.php',
	
	'WPV_Shortcode_Control_Post_Relationship' => dirname( __FILE__ ) . '/models/shortcode/control/post_relationship.php',
	'WPV_Shortcode_Control_Post_Ancestor' => dirname( __FILE__ ) . '/models/shortcode/control/post_ancestor.php',
	'WPV_Shortcode_Control_Post_Ancestor_From_Postmeta' => dirname( __FILE__ ) . '/models/shortcode/control/post_ancestor_from_postmeta.php',
	'WPV_Shortcode_Control_Post_Ancestor_From_M2m' => dirname( __FILE__ ) . '/models/shortcode/control/post_ancestor_from_m2m.php',
	// Sections
	'WPV_Section_Content_Selection' => dirname( __FILE__ ) . '/controllers/admin/sections/content_selection.php',
	// Filters
	'WPV_Filter_Base' => dirname( __FILE__ ) . '/controllers/filters/base.php',
	'WPV_Filter_Manager' => dirname( __FILE__ ) . '/controllers/filters/manager.php',
	'WPV_Filter_Post_Relationship' => dirname( __FILE__ ) . '/controllers/filters/post/relationship.php',
	'WPV_Filter_Post_Relationship_Gui' => dirname( __FILE__ ) . '/controllers/filters/post/relationship/gui.php',
	'WPV_Filter_Post_Relationship_Query' => dirname( __FILE__ ) . '/controllers/filters/post/relationship/query.php',
	'WPV_Filter_Post_Relationship_Search' => dirname( __FILE__ ) . '/controllers/filters/post/relationship/search.php',
);
