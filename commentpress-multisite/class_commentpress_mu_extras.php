<?php /*
================================================================================
Class CommentPressMultisiteExtras Version 1.0
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class overrides and customises some Multisite functionality

TODO
====

Merge this into the existing plugin...

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentPressMultisiteExtras {






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
	function CommentPressMultisiteExtras( $parent_obj = null ) {
		
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
	Methods to be merged
	----------------------------------------------------------------------------
	*/
	
	/** 
	 * @description: amend the post title prefix
	 * @todo: 
	 *
	 */
	function new_post_title_prefix( $prefix ) {
		
		// don't use a prefix
		return '';
	
	}
	
	
	
	
	
	/** 
	 * @description: add suffix " - Draft N", where N is the latest version number
	 * @todo: 
	 *
	 */
	function new_post_title( $title, $post ) {
	
		// get incremental version number of source post
		$key = '_cp_version_count';
		
		// if the custom field of our current post has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
			// get current value
			$value = get_post_meta( $post->ID, $key, true );
			
			// increment
			$value++;
			
		} else {
		
			// this must be the first new version (Draft 2)
			$value = 2;
		
		}
		
		
		
		// do we already have our suffix in the title?
		if ( stristr( $title, ' - Draft ' ) === false ) {
		
			// no, append " - Draft N"
			$title = $title.' - Draft '.$value;
			
		} else {
		
			// yes, split
			$title_array = explode( ' - Draft ', $title );
			
			// append to first part
			$title = $title_array[0].' - Draft '.$value;
			
		}
		
		
		
		// --<
		return $title;
	
	}
	
	
	
	
	

	/** 
	 * @description: WP < 3.4: override the the theme that is made active. This must be the theme NAME
	 * @todo: 
	 *
	 */
	function groupblog_theme_name( $existing ) {
	
		// switch to Demo theme
		return 'Commentpress Child Theme';
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: WP3.4+: override the theme that is made active. This must be the theme SLUG
	 * @todo: 
	 *
	 */
	function groupblog_theme_slug( $existing ) {
	
		// switch to Demo theme
		return 'commentpress-demo';
		
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
		
		// override theme that is activated (pre-WP3.4)
		add_filter( 'cp_groupblog_theme_name', array( $this, 'groupblog_theme_name' ), 21 );

		// override theme that is activated (WP3.4+)
		add_filter( 'cp_groupblog_theme_slug', array( $this, 'groupblog_theme_slug' ), 21 );
		
		// add filter for new post title prefix
		add_filter( 'cp_new_post_title_prefix', array( $this, 'new_post_title_prefix' ), 21, 1 );

		// add filter for new post title
		add_filter( 'cp_new_post_title', array( $this, 'new_post_title' ), 21, 2 );

	}
	
	
	



//##############################################################################
	
	
	



} // class ends
	
	
	



