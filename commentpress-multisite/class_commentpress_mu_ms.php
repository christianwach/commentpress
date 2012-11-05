<?php /*
================================================================================
Class CommentPressMultiSite Version 1.0
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class encapsulates all Multisite compatibility

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentPressMultiSite {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// admin object reference
	var $db;
	
	// MS: Commentpress-enabled on all sites, default is "false"
	var $cpmu_force_commentpress = '0';
	
	// MS: default title page content
	var $cpmu_title_page_content = '';
	
	// MS: allow translation workflow, default is "off"
	var $cpmu_disable_translation_workflow = '1';
	
	
	



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
	
		// store reference to database wrapper (child of calling obj)
		$this->db = $this->parent_obj->db;
	
		// init
		$this->_init();

		// --<
		return $this;

	}
	
	
	



	/**
	 * PHP 4 constructor
	 */
	function CommentPressMultiSite( $parent_obj = null ) {
		
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
	
	
	



	/** 
	 * @description: add an admin page for this plugin
	 * @todo: 
	 *
	 */
	function add_admin_menu() {
		
		// we must be network admin
		if ( !is_super_admin() ) { return false; }
		
		
	
		// try and update options
		$saved = $this->db->options_update();
		


		// always add the admin page to the Settings menu
		$page = add_submenu_page( 
		
			'settings.php', 
			__( 'Commentpress', 'commentpress-plugin' ), 
			__( 'Commentpress', 'commentpress-plugin' ), 
			'manage_options', 
			'cpmu_admin_page', 
			array( $this, '_network_admin_form' )
			
		);
		
		// add styles only on our admin page, see:
		// http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Load_scripts_only_on_plugin_pages
		add_action( 'admin_print_styles-'.$page, array( $this, 'add_admin_styles' ) );
	
	}
	
	
	



	/**
	 * @description: enqueue any styles and scripts needed by our admin page
	 * @todo: 
	 *
	 */
	function add_admin_styles() {
		
		/*
		// EXAMPLES:
		
		// add css
		wp_enqueue_style('cpmu-admin-style', CP_PLUGIN_URL . 'css/admin.css');
		
		// add javascripts
		wp_enqueue_script( 'cpmu-admin-js', CP_PLUGIN_URL . 'js/admin.js' );
		*/
		
		// add admin css
		wp_enqueue_style(
			
			'cpmu-admin-style', 
			CP_PLUGIN_URL . 'commentpress-multisite/css/admin.css',
			null,
			CPMU_PLUGIN_VERSION,
			'all' // media
			
		);
		
	}
	
	
	



	/**
	 * @description: enqueue any styles and scripts needed by our public pages
	 * @todo: 
	 *
	 */
	function add_frontend_styles() {
		
		/*
		// EXAMPLES:
		
		// add javascripts
		wp_enqueue_script( 
			
			'cpmu-admin-js', 
			CP_PLUGIN_URL . 'js/admin.js' 
			
		);
		*/
		
		// add css for signup form
		wp_enqueue_style( 
		
			'cpmu-signup-style', 
			CP_PLUGIN_URL . 'commentpress-multisite/css/signup.css',
			null,
			CPMU_PLUGIN_VERSION,
			'all' // media
			
		);
		
	}
	
	
	



	/** 
	 * @description: hook into the blog signup form
	 * @todo: 
	 *
	 */
	function signup_blogform( $errors ) {
	
		// only apply to wordpress signup form (not the BuddyPress one)
		if ( is_object( $this->parent_obj->bp ) ) { return; }


		
		// get force option
		$forced = $this->db->option_get( 'cpmu_force_commentpress' );
		
		// are we force-enabling Commentpress?
		if ( $forced ) {
			
			// set hidden element
			$forced_html = '
			<input type="hidden" value="1" id="cpmu-new-blog" name="cpmu-new-blog" />
			';

			// define text, but allow overrides
			$text = apply_filters( 
				'cp_multisite_options_signup_text_forced',
				__( 'Select the options for your new Commentpress document.', 'commentpress-plugin' )
			);
			
		} else {
		
			// set checkbox
			$forced_html = '
			<div class="checkbox">
				<label for="cpmu-new-blog"><input type="checkbox" value="1" id="cpmu-new-blog" name="cpmu-new-blog" /> '.__( 'Enable Commentpress', 'commentpress-plugin' ).'</label>
			</div>
			';
					
			// define text, but allow overrides
			$text = apply_filters( 
				'cp_multisite_options_signup_text',
				__( 'Do you want to make the new site a Commentpress document?', 'commentpress-plugin' )
			);
			
		}
		
		

		// get workflow element
		$workflow_html = $this->_get_workflow();
	
		// get blog type element
		$type_html = $this->_get_blogtype();
	


		// construct form
		$form = '

		<br />
		<div id="cp-multisite-options">

			<h3>'.__( 'Commentpress:', 'commentpress-plugin' ).'</h3>

			<p>'.$text.'</p>

			'.$forced_html.'

			'.$workflow_html.'

			'.$type_html.'

		</div>

		';
		
		echo $form;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: hook into wpmu_new_blog and target plugins to be activated
	 * @todo: 
	 *
	 */
	function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	
		// test for presence of our checkbox variable in _POST
		if ( isset( $_POST['cpmu-new-blog'] ) AND $_POST['cpmu-new-blog'] == '1' ) {
			
			// hand off to private method
			$this->_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

		}
		
	}
	

	
	
	
	
	/**
	 * Change the greeting in the WordPress Admin Bar
	 * Props: http://pankajanupam.in
	 */
	function change_admin_greeting( $translated, $text, $domain ) {
		
		// look only for default WordPress translations
		if ('default' != $domain) { return $translated; }
		
		// catch all instances of 'Howdy'...
		if ( false !== strpos( $translated, 'Howdy' ) ) {
			return str_replace( 'Howdy', 'Welcome', $translated );
		}
		
		// --<
		return $translated;
		
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
		
		// add form elements to signup form
		add_action( 'signup_blogform', array( $this, 'signup_blogform' ) );
		
		// activate blog-specific Commentpress plugin
		add_action( 'wpmu_new_blog', array( $this, 'wpmu_new_blog' ), 12, 6 );
		
		// enable/disable workflow sitewide
		add_filter( 'cp_class_commentpress_workflow_enabled', array( $this, '_get_workflow_enabled' ) );
	
		// is this the back end?
		if ( is_admin() ) {
	
			// add menu to Network submenu
			add_action( 'network_admin_menu', array( $this, 'add_admin_menu' ), 30 );
		
			// add options to reset array
			add_filter( 'cpmu_options_reset', array( $this, '_get_default_settings' ), 20 );
				
			// hook into Network BuddyPress form update
			add_action( 'cpmu_db_options_update', array( $this, '_network_admin_update' ), 20 );
			
		} else {
		
			// register any public styles
			add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_styles' ), 20 );
			
		}
		
		// override Title Page content
		add_filter( 'cp_title_page_content', array( $this, '_get_title_page_content' ) );
		
		// change that infernal howdy
		add_filter( 'gettext', array( $this, 'change_admin_greeting' ), 40, 3 );
	
	}
	
	
	



	/** 
	 * @description: create a blog
	 * @todo:
	 *
	 */
	function _create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	
		// wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again
		switch_to_blog( $blog_id );
		
		// activate Commentpress core
		$this->db->install_commentpress();
		
		// switch back
		restore_current_blog();
		
	}
	
	
	



	/** 
	 * @description: get workflow form elements
	 * @return: form html
	 *
	 */
	function _get_workflow() {
	
		// init
		$workflow_html = '';
	
		// get data
		$workflow = $this->db->get_workflow_data();
		
		// if we have workflow data...
		if ( !empty( $workflow ) ) {
		
			// show it
			$workflow_html = '
			
			<div class="checkbox">
				<label for="cp_blog_workflow">'.$workflow['element'].' '.$workflow['label'].'</label>
			</div>

			';
		
		}
		
		// --<
		return $workflow_html;
		
	}
	
	
	



	/** 
	 * @description: get blog type form elements
	 *
	 */
	function _get_blogtype() {
	
		// init
		$type_html = '';
	
		// get data
		$type = $this->db->get_blogtype_data();
		
		// if we have type data...
		if ( !empty( $type ) ) {
		
			// show it
			$type_html = '
			
	<div class="dropdown">
		<label for="cp_blog_type">'.$type['label'].'</label> <select id="cp_blog_type" name="cp_blog_type">
		
		'.$type['element'].'
		
		</select>
	</div>

			';
		
		}
		
		
		
		// --<
		return $type_html;
		
	}
	
	
	



	/**
	 * @description: show our admin page
	 * @todo: 
	 *
	 */
	function _network_admin_form() {
	
		// only allow network admins through
		if( is_super_admin() == false ) {
			
			// disallow
			wp_die( __( 'You do not have permission to access this page.', 'commentpress-plugin' ) );
			
		}
		
		
		
		//_cpdie( 'here' );

		// show message
		if ( isset( $_GET['updated'] ) ) {
			echo '<div id="message" class="updated"><p>'.__( 'Options saved.', 'commentpress-plugin' ).'</p></div>';
		}
		


		// sanitise admin page url
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( is_array( $url_array ) ) { $url = $url_array[0]; }
		
		
		
		// open admin page
		echo '
<div class="wrap" id="cpmu_admin_wrapper">

<div class="icon32" id="icon-options-general"><br/></div>

<h2>'.__( 'Commentpress Network Settings', 'commentpress-plugin' ).'</h2>

<form method="post" action="'.htmlentities($url.'&updated=true').'">

'.wp_nonce_field( 'cpmu_admin_action', 'cpmu_nonce', true, false ).'
'.wp_referer_field( false ).'



';


		
		// show multisite options
		echo '
<div id="cpmu_admin_options">

<h3>'.__( 'Multisite Settings', 'commentpress-plugin' ).'</h3>

<p>'.__( 'Configure how your Commentpress Network behaves. Site-specific options are set on the Commentpress Settings page for that site.', 'commentpress-plugin' ).'</p>';
		
		
		// add global options
		echo '
<h4>'.__( 'Global Options', 'commentpress-plugin' ).'</h4>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cpmu_reset">'.__( 'Reset Multisite options', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_reset" name="cpmu_reset" value="1" type="checkbox" /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_force_commentpress">'.__( 'Make all new sites Commentpress-enabled', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_force_commentpress" name="cpmu_force_commentpress" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_force_commentpress' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_disable_translation_workflow">'.__( 'Disable Translation Workflow (Recommended because it is still very experimental)', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_disable_translation_workflow" name="cpmu_disable_translation_workflow" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_disable_translation_workflow' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

'.$this->_additional_multisite_options().'

</table>';


		/*
		// add WordPress overrides
		echo '
<h4>'.__( 'Override WordPress behaviour', 'commentpress-plugin' ).'</h4>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cpmu_delete_first_page">'.__( 'Delete WordPress-generated Sample Page', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_delete_first_page" name="cpmu_delete_first_page" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_delete_first_page' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_delete_first_post">'.__( 'Delete WordPress-generated Hello World post', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_delete_first_post" name="cpmu_delete_first_post" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_delete_first_post' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_delete_first_comment">'.__( 'Delete WordPress-generated First Comment', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_delete_first_comment" name="cpmu_delete_first_comment" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_delete_first_comment' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

</table>';
		*/


		// close form
		echo '
</div>';

		
		
		/*
		// title
		echo '<h3>'.__( 'Title Page Content', 'commentpress-plugin' ).'</h3>';

		// explanation
		echo '<p>'.__( 'The following is the content of the Title Page for each new Commentpress site. Edit it if you want to show something else on the Title Page.', 'commentpress-plugin' ).'</p>';

		// get content
		$content = stripslashes( $this->db->option_get( 'cpmu_title_page_content' ) );
		//_cpdie( $content );
		
		// call the editor
		wp_editor( 
		
			$content, 
			'cpmu_title_page_content', 
			$settings = array(
		
				'media_buttons' => false
			
			)
			
		);
		*/



		// allow plugins to add stuff
		echo $this->_additional_form_options();


		
		// close admin form
		echo '
<p class="submit">
	<input type="submit" name="cpmu_submit" value="'.__( 'Save Changes', 'commentpress-plugin' ).'" class="button-primary" />
</p>

</form>

</div>
'."\n\n\n\n";



	}
	
	
	



	/**
	 * @description: allow other plugins to hook into our multisite admin options
	 * @todo: 
	 *
	 */
	function _additional_multisite_options() {
	
		// return whatever plugins send
		return apply_filters(
			'cpmu_network_multisite_options_form', 
			''
		);
	
	}
	
	
	



	/**
	 * @description: allow other plugins to hook into our admin form
	 * @todo: 
	 *
	 */
	function _additional_form_options() {
	
		// return whatever plugins send
		return apply_filters(
			'cpmu_network_options_form', 
			''
		);
	
	}
	
	
	



	/**
	 * @description: get default BuddyPress-related settings
	 * @todo: 
	 *
	 */
	function _get_default_settings() {
		
		// return options array
		return array(
			
			// default Multisite options
			'cpmu_force_commentpress' => $this->cpmu_force_commentpress,
			'cpmu_title_page_content' => $this->cpmu_title_page_content,
			'cpmu_disable_translation_workflow' => $this->cpmu_disable_translation_workflow
			
		);

		
	}
	
	
	



	/**
	 * @description: allow other plugins to hook into our admin form
	 * @todo: 
	 *
	 */
	function _get_workflow_enabled() {
	
		// get option
		$disabled = $this->db->option_get( 'cpmu_disable_translation_workflow' ) == '1' ? false : true;
		//_cpdie( array( $disabled ) );
		
		// return whatever option is set
		return $disabled;
	
	}
	
	
	



	/** 
	 * @description: get default Title Page content, if set
	 * @todo: 
	 *
	 */
	function _get_title_page_content( $content ) {
		
		/*
		// disabled until we enable the admin page editor
		
		// get content
		$overridden_content = stripslashes( $this->db->option_get( 'cpmu_title_page_content' ) );
		
		// is it different to what's been passed?
		if ( $content != $overridden_content ) {
		
			// override
			$content = $overridden_content;
		
		}
		*/

		// --<
		return $content;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: hook into Network form update
	 * @todo: 
	 *
	 */
	function _network_admin_update() {
		
		// database object
		global $wpdb;
		
		// init
		$cpmu_force_commentpress = '0';
		$cpmu_title_page_content = '';
		$cpmu_disable_translation_workflow = '0';
		
		// get variables
		extract( $_POST );
		
		// force all new sites to be Commentpress-enabled
		$cpmu_force_commentpress = $wpdb->escape( $cpmu_force_commentpress );
		$this->db->option_set( 'cpmu_force_commentpress', ( $cpmu_force_commentpress ? 1 : 0 ) );
		
		// default title page content
		$cpmu_title_page_content = $wpdb->escape( $cpmu_title_page_content );
		$this->db->option_set( 'cpmu_title_page_content', $cpmu_title_page_content );
		
		// allow translation workflow
		$cpmu_disable_translation_workflow = $wpdb->escape( $cpmu_disable_translation_workflow );
		$this->db->option_set( 'cpmu_disable_translation_workflow', ( $cpmu_disable_translation_workflow ? 1 : 0 ) );
		
	}
	
	
	
	
	
	
//##############################################################################
	
	
	



} // class ends
	
	
	



?>