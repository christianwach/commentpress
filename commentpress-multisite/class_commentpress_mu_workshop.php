<?php /*
================================================================================
Class CommentPressGroupBlogWorkshop
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class overrides the name of Groupblogs from "Blog" (or "Document") to "Workshop"

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentPressGroupBlogWorkshop {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	
	



	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj = null ) {
	
		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;
	
		// init
		$this->_init();

		// --<
		return $this;

	}
	
	
	



	/**
	 * PHP 4 constructor
	 */
	function CommentPressGroupBlogWorkshop( $parent_obj = null ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	function initialise() {
	
	}
	
	
	



	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
	}
	
	
	



//##############################################################################
	
	
	



	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	
	
	



	/*
	----------------------------------------------------------------------------
	BuddyPress Groupblog Text Overrides
	----------------------------------------------------------------------------
	*/
	
	/**
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function groupblog_comment_name() { 
	
		// default name
		return __( 'Workshop Comments', 'commentpress-plugin' );
		
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function groupblog_post_name() {
	
		// default name
		return __( 'Workshop Posts', 'commentpress-plugin' );
	
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function activity_post_name() {
	
		// default name
		return __( 'workshop post', 'commentpress-plugin' );
	
	}
	
	
	



	/** 
	 * @description: override the name of the sub-nav item
	 * @todo: 
	 *
	 */
	function filter_blog_name( $name ) {
	
		return __( 'Workshop', 'commentpress-plugin' );
		
	}
	
	
	
	
	
	
	/** 
	 * @description: override the slug of the sub-nav item
	 * @todo: 
	 *
	 */
	function filter_blog_slug( $slug ) {
	
		return 'workshop';
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override the title of the "Recent Comments in..." link
	 * @todo: 
	 *
	 */
	function activity_tab_recent_title_blog( $title ) {
	
		// if groupblog...
		global $commentpress_obj;
		if ( 
		
			!is_null( $commentpress_obj ) 
			AND is_object( $commentpress_obj ) 
			AND $commentpress_obj->is_groupblog() 
			
		) { 
		
			// override default link name
			return apply_filters(
				'cpmsextras_user_links_new_site_title', 
				__( 'Recent Comments in this Workshop', 'commentpress-plugin' )
			);
			
		}
		
		// if main site...
		if ( is_multisite() AND is_main_site() ) { 
		
			// override default link name
			return apply_filters(
				'cpmsextras_user_links_new_site_title', 
				__( 'Recent Comments in Site Blog', 'commentpress-plugin' )
			);
			
		}
		
		return $title;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override title on All Comments page
	 * @todo: 
	 *
	 */
	function page_all_comments_blog_title( $title ) {
	
		// --<
		return __( 'Comments on Workshop Posts', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on All Comments page
	 * @todo: 
	 *
	 */
	function page_all_comments_book_title( $title ) {
	
		// --<
		return __( 'Comments on Workshop Pages', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on Activity tab
	 * @todo: 
	 *
	 */
	function filter_activity_title_all_yours( $title ) {
	
		// --<
		return __( 'Recent Activity in your Workshops', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on Activity tab
	 * @todo: 
	 *
	 */
	function filter_activity_title_all_public( $title ) {
	
		// --<
		return __( 'Recent Activity in Public Workshops', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override Commentpress "Title Page"
	 * @todo: 
	 *
	 */
	function filter_nav_title_page_title( $title ) {
		
		// access globals
		global $commentpress_obj;

		// if plugin active...
		if ( 
		
			!is_null( $commentpress_obj ) 
			AND is_object( $commentpress_obj )
			AND $commentpress_obj->is_groupblog()
			
		) {
		
			// --<
			return __( 'Workshop Home Page', 'commentpress-plugin' );
			
		}
		
		// --<
		return $title;
	
	}
	
	
	
	
	

	/** 
	 * @description: override the BP Sites Directory "visit" button
	 * @todo: 
	 *
	 */
	function get_blogs_visit_blog_button( $button ) {
		
		//print_r( $button ); die();
		
		global $blogs_template;
		if( !get_groupblog_group_id( $blogs_template->blog->blog_id ) ) {
		
			// check site_options to see if site is a Commentpress-enabled one
			
			// otherwise, leave the button untouched
			
		} else {
			
			// update link for groupblogs
			$label = __( 'Visit Workshop', 'commentpress-plugin' );
			$button['link_text'] = $label;
			$button['link_title'] = $label;
		
		}
		
		// --<
		return $button;
	
	}
	
	
	
	
	
	
//##############################################################################
	
	
	



	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// register hooks
		$this->_register_hooks();
		
	}
	
	
	



	/** 
	 * @description: register Wordpress hooks
	 * @todo: 
	 *
	 */
	function _register_hooks() {
		
		// override Commentpress "Title Page"
		add_filter( 'cp_nav_title_page_title', array( $this, 'filter_nav_title_page_title' ), 21 );
		
		// override CP title of "view document" button in blog lists
		add_filter( 'bp_get_blogs_visit_blog_button', array( $this, 'get_blogs_visit_blog_button' ), 21 );

		// filter bp-groupblog defaults
		add_filter( 'bp_groupblog_subnav_item_name', array( $this, 'filter_blog_name' ), 21 );
		add_filter( 'bp_groupblog_subnav_item_slug', array( $this, 'filter_blog_slug' ), 21 );
		
		// change name of activity sidebar headings
		add_filter( 'cp_activity_tab_recent_title_all_yours', array( $this, 'filter_activity_title_all_yours' ), 21 );
		add_filter( 'cp_activity_tab_recent_title_all_public', array( $this, 'filter_activity_title_all_public' ), 21 );
		
		// override with 'workshop'
		add_filter( 'cp_activity_tab_recent_title_blog', array( $this, 'activity_tab_recent_title_blog' ), 21, 1 );
		
		// override titles of BP activity filters
		add_filter( 'cp_groupblog_comment_name', array( $this, 'groupblog_comment_name' ), 21 );
		add_filter( 'cp_groupblog_post_name', array( $this, 'groupblog_post_name' ), 21 );
		
		// cp_activity_post_name_filter
		add_filter( 'cp_activity_post_name', array( $this, 'activity_post_name' ), 21 );
		
		// override label on All Comments page
		add_filter( 'cp_page_all_comments_book_title', array( $this, 'page_all_comments_book_title' ), 21, 1 );
		add_filter( 'cp_page_all_comments_blog_title', array( $this, 'page_all_comments_blog_title' ), 21, 1 );
		
	}
	
	
	



//##############################################################################
	
	
	



} // class ends
	
	
	



