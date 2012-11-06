/*
===============================================================
Commentpress addComment Javascript
===============================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
---------------------------------------------------------------
NOTES

This script is only included when the Commentpress theme is active.

The moveForm() method is called by onclick attributes of the 
"Reply to this comment" links, which are auto-generated by WP.

This is a rewritten version of the inbuilt Wordpress addComment 
object for several reasons:

(1) The built-in Wordpress Javascript does not allow for the 
enabling of TinyMCE, *even though* Wordpress now ships with it 
built-in to the Admin Interface. TinyMCE must be de-activated 
before the respond div is moved, then re-enabled once the move 
has been completed.

(2) There is a name clash between TinyMCE's wrapper and the 
comment_parent input when comment threading is enabled. Both use 
id="comment_parent", thus preventing that variable from being 
posted when the form is submitted.

(3) Commentpress has a replytopara link parameter and an additional 
text_signature variable which need to be accounted for. See the 
moveFormToPara() method for details of the latter.

---------------------------------------------------------------
*/






/** 
 * @description: comment area handler object
 * @todo:
 *
 */	
addComment = {



	/** 
	 * @description: method for moving the comment form
	 * @todo:
	 *
	 */	
	moveForm : function( commentID, parentID, respondID, postID, textSig ) {
	

		
		// unload tinyMCE
		this.disableForm();
		


		// properties
		var div_e;
		var comm_e = this.I(commentID);
		var respond_e = this.I(respondID);
		var cancel_e = this.I('cancel-comment-reply-link');
		var parent_e = this.I('comment_parent');
		var post_e = this.I('comment_post_ID');
		// get comment text signature item
		if ( this.I('text_signature') ) {
			var sig_e = this.I('text_signature');
		} else {
			var sig_e = '';
		}
		


		// sanity check
		if ( !comm_e || !respond_e || !cancel_e || !parent_e ) {
		
			// reload tinyMCE
			this.enableForm();
			
			// --<
			return;
			
		}


		
		// if we have them...
		if ( post_e && postID ) {
		
			// set comment_post_ID hidden input to postID
			post_e.value = postID;
			
		}
		
		// set comment_parent hidden input to parentID
		parent_e.value = parentID;
		//console.log( 'parent_e.value set: ' + parent_e.value );
		
		// set text_signature hidden input to text signature
		if ( sig_e !== '' ) { sig_e.value = textSig; }

		// store respondID for cancel method to access
		this.respondID = respondID;
		
		// set title
		addComment.setTitle( parentID, textSig, 'set' );
		
		
		
		// do we have a temp div?
		if ( !this.I('wp-temp-form-div') ) {
			
			// create one
			div_e = document.createElement('div');
			div_e.id = 'wp-temp-form-div';
			div_e.style.display = 'none';
			respond_e.parentNode.insertBefore( div_e, respond_e );
			
		}



		// insert comment response area
		comm_e.parentNode.insertBefore( respond_e, comm_e.nextSibling );
		
		
		
		// if not special page and we encouraging commenting and not a reply
		if ( cp_special_page != '1' && cp_promote_reading == '0' && parentID == '0' ) {
			
			// hide cancel link
			cancel_e.style.display = 'none';

		} else {
		
			// show cancel link
			cancel_e.style.display = '';
	
		}
		
		
		
		/** 
		 * @description: method for cancel button
		 * @todo:
		 *
		 */	
		cancel_e.onclick = function() {
		
			// --<
			return addComment.cancelForm();
			
		}

		
		
		// test for tinyMCE
		if ( cp_tinymce == '1' ) {
		
			// reload tinyMCE
			this.enableForm();
		
		} else {
		
			// try and give focus to textarea - disabled since we use tinyMCE
			try { this.I('comment').focus(); }
			catch(e) {}
			
		}
		


		// show respond element
		respond_e.style.display = 'block';
		
		
		
		// clear comment highlight
		addComment.clearCommentHighlight( this.parentID );
			
		// highlight
		addComment.highlightComment( parentID );
		
		
		
		// store text sig
		this.text_signature = textSig;
		this.parentID = parentID;
		


		// --<
		return false;
		
	},
	
	
	
	/** 
	 * @description: method for moving the comment form to a paragraph block
	 * @todo:
	 *
	 */	
	moveFormToPara : function( paraNum, textSig, postID ) {
		
		// set paraID
		var paraID = 'reply_to_para-' + paraNum;



		// move the form
		addComment.moveForm( 
		
			paraID, 
			'0', 
			'respond', 
			postID,
			textSig
			
		);
		
		
		
		// --<
		return false;
		
	},
	

		
	/** 
	 * @description: utility reset form
	 * @todo:
	 *
	 */	
	cancelForm : function() {
		
		// get our temp div element
		var temp_e = addComment.I('wp-temp-form-div');
		
		// get our comment response element
		var respond_e = addComment.I(addComment.respondID);
		
		// get cancel button
		var cancel_e = this.I('cancel-comment-reply-link');

		// sanity check
		if ( !temp_e || !respond_e ) {
		
			// --<
			return;
			
		}


		
		// clear comment highlight
		addComment.clearCommentHighlight( this.parentID );
			


		// if not special page
		if ( cp_special_page != '1' ) {

			// init text_sig
			var text_sig = '';
			var para_num = '';
			
			// if we have a text sig
			if ( addComment.I('text_signature') ) {
			
				// unset comment text signature value
				text_sig = addComment.I('text_signature').value;
				addComment.I('text_signature').value = '';
				
				//console.log('text_sig: '+text_sig);

				// This is a potential source of weakness: if the para text has been changed,
				// but not by much, then levenshtein will still associate the comment with
				// a paragraph, but there will be no *exact* reference in the DOM.

				// find para num
				var para_id = jQuery('#para_wrapper-' + text_sig + ' .reply_to_para').attr('id');
				
				// is there an element for the exact match?
				if ( para_id === undefined ) {
				
					// NO -> crawl up the DOM looking for the wrapper
					var parent_wrapper = jQuery('#respond').closest('div.paragraph_wrapper');
					
					// if we get it...
					if ( parent_wrapper.length > 0 ) {
					
						// grab it's id
						var parent_wrapper_id = parent_wrapper.attr('id');
						
						// proceed with this instead
						var para_id = jQuery( '#' + parent_wrapper_id + ' .reply_to_para').attr('id');

					}

				}
				
				// get paragraph number
				para_num = para_id.split('-')[1];

			}
		
			
			
			// are we encouraging reading?
			if ( cp_promote_reading == '1' ) {
				
				// hide respond element
				if ( respond_e.style.display != 'none' ) {
					respond_e.style.display = 'none';
				}
				
			} else {
			
				// get comment post ID
				var post_id = addComment.I('comment_post_ID').value;
				
				// return form to para position
				
				// return form to para
				addComment.moveFormToPara( para_num, text_sig, post_id );
				
				return false;
	
			}
				
		} else {
			
			//addComment.highlightComment( this.parentID );
			
		}
		


		// unload tinyMCE
		addComment.disableForm();



		// get comment post ID
		var parent_id = addComment.I('comment_parent').value;

		// unset comment parent value
		addComment.I('comment_parent').value = '0';


		
		// DOM manipulation
		temp_e.parentNode.insertBefore( respond_e, temp_e );
		temp_e.parentNode.removeChild( temp_e );
		
		
		
		// hide cancel link
		cancel_e.style.display = 'none';
		
		// disable this until next run
		cancel_e.onclick = null;
		
		
		
		// set title
		addComment.setTitle( '0', text_sig, 'cancel' );
		


		// clear text sig
		this.text_signature = '';
		


		// reload tinyMCE
		addComment.enableForm();



		// --<
		return false;
		
	},
	
	
	
	/** 
	 * @description: utility get element ID method
	 * @todo:
	 *
	 */	
	I : function(e) {
	
		// --<
		return document.getElementById(e);
		
	},
	
	
	
	/** 
	 * @description: utility method for enabling the comment form
	 * @todo:
	 *
	 */	
	enableForm : function() {

		// test for tinyMCE
		if ( cp_tinymce == '1' ) {
		
			// load tinyMCE, changed from this: tinyMCE.execCommand('mceAddControl', false, 'comment');
			setTimeout( function() { tinyMCE.execCommand('mceAddControl', false, 'comment'); }, 1 );
			//console.log( 'control added' );
			
		}
		
	},

		
	/** 
	 * @description: utility method for disabling the comment form
	 * @todo:
	 *
	 */	
	disableForm : function() {

		// test for tinyMCE
		if ( cp_tinymce == '1' ) {
		
			// unload tinyMCE
			tinyMCE.execCommand('mceRemoveControl', false, 'comment');
			//console.log( 'control removed' );
			
		}
		
	},

		

	/** 
	 * @description: utility setter
	 * @todo:
	 *
	 */	
	setTitle : function( parentID, textSig, mode ) {
	
		//console.log( 'parentID: '+parentID+' textSig: '+textSig+' mode: ' + mode );
	
		// get comment form title item
		var title = addComment.I('respond_title');

		// is it a comment reply?
		if ( parentID === undefined || parentID == '0' ) {
		
			// NO -> is it a comment on the whole page?
			if ( textSig === undefined || textSig == '' ) {
			
				// if special page
				if ( cp_special_page == '1' ) {
				
					// restore
					title.innerHTML = 'Leave a comment';
					
				} else {
				
					// restore
					//title.innerHTML = 'Comment on the page';
					title.innerHTML = jQuery( '#para_wrapper-' + textSig + ' a.reply_to_para' ).text();
					
					// get comment list
					var comment_list = jQuery( '#para_wrapper-' + addComment.text_signature + ' .commentlist' );
					
					// if we have a comment list...
					if ( comment_list[0] && cp_promote_reading == '0' ) {
						jQuery( '#para_wrapper-' + addComment.text_signature + ' div.reply_to_para' ).show();
					}
					jQuery( '#para_wrapper-' + textSig + ' div.reply_to_para' ).hide();
					
				}
					
			} else {
			
				// it's a comment on a paragraph
				//title.innerHTML = 'Comment on this paragraph';
				title.innerHTML = jQuery( '#para_wrapper-' + textSig + ' a.reply_to_para' ).text();
	
				// get comment list
				var comment_list = jQuery( '#para_wrapper-' + addComment.text_signature + ' .commentlist' );
				
				// if we have a comment list and promoting commenting (or promoting reading)...
				if ( ( comment_list[0] && cp_promote_reading == '0' ) || cp_promote_reading == '1' ) {
					
					// show previous reply to para link
					if ( addComment.text_signature !== undefined ) {
						jQuery( '#para_wrapper-' + addComment.text_signature + ' div.reply_to_para' ).show();
					}
					
				}

				// sort out reply to para links
				if ( cp_promote_reading == '0' ) {
					jQuery( '#para_wrapper-' + textSig + ' div.reply_to_para' ).hide();
				} else {
					if ( mode == 'cancel' ) {
						jQuery( '#para_wrapper-' + textSig + ' div.reply_to_para' ).show();
					} else {
						jQuery( '#para_wrapper-' + textSig + ' div.reply_to_para' ).toggle();
					}
				}
				
			}
			
		} else {
		
			// it's a reply to another comment
			
			// store
			//addComment.replyTitle = title.innerHTML;
			//console.log( jQuery( '#comment-' + parentID + ' > .reply' ) );
			//console.log( jQuery( '#comment-' + parentID + ' > .reply' ).text() );
			
			// seems like sometimes we can get an array for the .reply with more than one item...
			var reply = jQuery( '#comment-' + parentID + ' > .reply' )[0];
			
			// get unique
			var unique = jQuery(reply).text();
			
			// if we have link text, then a comment reply is allowed...
			if ( unique != '' ) {
			
				// get reply link text
				title.innerHTML = unique;
				
				// sanitise textSig
				if ( textSig === undefined || textSig == '' ) { textSig == ''; }
				
				// if promoting commenting, sort out reply to para links
				if ( cp_promote_reading == '1' ) {
					
					// show previous
					if ( addComment.text_signature !== undefined ) {
						jQuery( '#para_wrapper-' + addComment.text_signature + ' div.reply_to_para' ).show();
					}
					
					// show current
					jQuery( '#para_wrapper-' + textSig + ' div.reply_to_para' ).show();
					
				}
				
			}
			
		}
		
	},



	/** 
	 * @description: highlight a comment
	 * @todo:
	 *
	 */	
	highlightComment : function( parentID ) {

		// hide this reply link
		if ( parentID != '0' ) {
			jQuery( '#comment-' + parentID + ' > .reply' ).css('display', 'none');
		}
		
		// highlight comment
		jQuery( '#li-comment-' + parentID + ' > .comment-wrapper' ).css('background-color', '#CBFFBD');
		
		// get existing colour
		addComment.commentBorder = jQuery( '#comment-' + parentID + ' > .comment-content' ).css('border-bottom');
		
		// set highlight
		jQuery( '#comment-' + parentID + ' > .comment-content' ).css('border-bottom', '1px dashed #b8b8b8');
		
	},



	/** 
	 * @description: clear a comment highlight
	 * @todo:
	 *
	 */	
	clearCommentHighlight : function( parentID ) {

		// show this reply link
		if ( parentID != '0' ) {
		
			// show reply link
			jQuery( '#comment-' + parentID + ' > .reply' ).css('display', 'block');

		}
		
		// unhighlight comment
		jQuery( '#li-comment-' + parentID + ' > .comment-wrapper' ).css('background-color', '#fff');
		jQuery( '#comment-' + parentID + ' > .comment-content' ).css('border-bottom', addComment.commentBorder);
		
	},



	/** 
	 * @description: clear all comment highlights
	 * @todo:
	 *
	 */	
	clearAllCommentHighlights : function() {

		// show all reply links
		jQuery( '.reply' ).css('display', 'block');
		
		// clear highlight
		jQuery( '.comment-wrapper' ).css('background-color', '#fff');
		jQuery( '#comment-' + parentID + ' > .comment-content' ).css('border-bottom', addComment.commentBorder);

	},



	/** 
	 * @description: utility getter
	 * @todo:
	 *
	 */	
	getTextSig : function() {

		// --<
		return this.text_signature;

	},



	/** 
	 * @description: utility getter
	 * @todo:
	 *
	 */	
	getLevel : function() {
	
		// is the comment on the paragraph?
		if ( this.parentID === undefined || this.parentID === '0' ) {
		
			return true;
			
		} else {
		
			return false;
		
		}

	}

}

