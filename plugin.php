jQuery(document).ready(function( $ ){

    // $('#default_add_comment_form textarea').textareaAutoExpand();

    /**
     * Default ajax setup
     */
    $.ajaxSetup({
        type: "POST",
        url: ajaxurl,
        dataType: "html"
    });


    window.inline_comments_ajax_load_template = function( params, my_global ) {

        var my_global;
        var request_in_process = false;

        params.action = "inline_comments_load_template";

        $.ajax({
            data: params,
            global: my_global,
            success: function( msg ){
                $( params.target_div ).fadeIn().html( msg );
                request_in_process = false;
                if (typeof params.callback === "function") {
                    params.callback();
                }
                
                set_up_para_comments();
            }
        });
    }

    /**
     * Submit new comment, note comments are loaded via ajax
     * CK - add para-id to the ajax call
     */
     $( document ).on( 'submit', '#default_add_comment_form', function( event ){
        event.preventDefault();

        var $this = $(this);
        $this.css('opacity','0.5');

        data = {
            action: "inline_comments_add_comment",
            post_id: $('#inline_comments_ajax_handle').attr( 'data-post_id' ),
            user_name: $('#inline_comments_user_name').val(),
            user_email: $('#inline_comments_user_email').val(),
            user_url: $('#inline_comments_user_url').val(),
            comment: $( '#comment' ).val(),
            security: $('#inline_comments_nonce').val(),
            para_id: current_para_id
        };

        $.ajax({
            data: data,
            global: false,
            success: function( msg ){
                inline_comments_ajax_load_template({
                    "target_div": "#inline_comments_ajax_target",
                    "template": $( '#inline_comments_ajax_handle' ).attr( 'data-template' ),
                    "post_id": $( '#inline_comments_ajax_handle' ).attr( 'data-post_id' ),
                    "security": $( '#inline_comments_nonce' ).val()
                }, false );
                
                $('textarea').val('');
                $this.css('opacity','1');
                
                // CK - increment the comment count
                var comment_count_holder = $('p[data-para-id="' + current_para_id + '"] > span > a');
                var comment_count = parseInt( comment_count_holder.text() );
 				comment_count_holder.text( comment_count + 1 );                

            }
        });

    });

    /**
     * Allow Comment form to be submitted when the user
     * presses the "enter" key.
     */
    $( document ).on('keypress', '#default_add_comment_form textarea, #default_add_comment_form input', function( event ){
        if ( event.keyCode == '13' ) {
            event.preventDefault();
            $('#default_add_comment_form').submit();
        }
    });

    $( window ).load(function(){
    
        if ( $( '#inline_comments_ajax_handle' ).length ) {
            $( '.inline-comments-loading-icon').show();

            data = {
                "action": "inline_comments_load_template",
                "target_div": "#inline_comments_ajax_target",
                "template": $( '#inline_comments_ajax_handle' ).attr( 'data-template' ),
                "post_id": $( '#inline_comments_ajax_handle' ).attr( 'data-post_id' ),
                "security": $('#inline_comments_nonce').val()
            };

            $.ajax({
                data: data,
                success: function( msg ){
                    $( '.inline-comments-loading-icon').fadeOut();
                    $( "#inline_comments_ajax_target" ).fadeIn().html( msg ); // Give a smooth fade in effect
                    if ( location.hash ){
                        $('html, body').animate({
                            scrollTop: $( location.hash ).offset().top
                        });
                        $( location.hash ).addClass( 'inline-comments-highlight' );
                    }
                    
                    // ck change - call the setting up of the paragraphs when comments are loaded;
                    set_up_para_comments();
                }
            });

            $( document ).on('click', '.inline-comments-time-handle', function( e ){
                $( '.inline-comments-content' ).removeClass('inline-comments-highlight')
                comment_id = '#comment-' + $( this ).attr('data-comment_id');
                $( comment_id ).addClass('inline-comments-highlight');
            });
        }
    });

    $( document ).on('click', '.inline-comments-more-handle', function( event ){
        event.preventDefault();
        
       if ( $( this ).hasClass('inline-comments-more-open') ){
            $( 'a', this ).html('more');
            $('#comment').css('height', '0');
        } else {
            $( 'a', this ).html('less');
            $('#comment').css('height', '150');
        }
        $( this ).toggleClass('inline-comments-more-open');
        $('.inline-comments-more-container').toggle();
    });

	
	// paragraph commenting additions
	var current_para_id;

	window.set_up_para_comments = function() {
	
		if ( current_para_id ) {
		
			$( 'div.inline-comments-container .inline-comments-content' ).hide();
			$( 'div.comment-para-id-' + current_para_id ).show();
			$( 'div.inline-comments-content-comment-fields' ).show();
			
			return;
		
		}
		
		$( 'div#comments-container' ).hide();
	
		// set up paragraph ids	
		// WARNING! if text changes then hashCode will change and comments will be orphaned
		$('p').each( function( index ) {
		
			// generate hashcode
			var para_id = $( this ).text().hashCode();

			// add the hashcode as an id
			$( this ).addClass('para-id-' + para_id );
			
			// also store the para_id on the p so that we can use it later
			$( this ).attr( 'data-para-id' , para_id );
			
			// get comments
			var comments = $( 'div.comment-para-id-' + para_id );
			
			// hide them 
			comments.hide();
			
			// remove the orphan-comment class (only those comments without a matching p will be left)
			comments.removeClass('orphan-comment');
			
			// add the number of comments to the link
			$( this ).append('<span class="p-comment-count"><a href="#" class="toggle_comments">' + comments.length + '</a></span>');
	
		});
		
		var orphans = $('div.orphan-comment');
		
		if ( orphans.length > 0 ) {
		
			$('<div class="orphan-comments-container"><p>These are orphan comments - the paragraph they were attached to has either been edited or deleted.</p></div>').insertAfter('div[name="comments"]');
			$('div.orphan-comments-container').append( orphans );	
		
		}
		
	}
		
		
	// display comments on top
    $( document ).on('click', '.toggle_comments', function( event ){
        event.preventDefault();
	
		var container = $('div[name="comments"]');
				
		if ( container.css('display') == 'block' ) {
		
			container.hide();
			
			return;
		}
		
	
		// get the position of the clicked comment count
		var position = $( this ).position();
		
		//get the grandparent
		var para = $( this ).parent().parent();
		
		// get the para_id
		current_para_id = para.attr('data-para-id');

		// show only this paragraphs comments plus the new comment form			
		$( 'div.inline-comments-container .inline-comments-content' ).hide();
		$( 'div.comment-para-id-' + current_para_id ).show();
		$( 'div.inline-comments-content-comment-fields' ).show();
		
		// style the comment box
		container.css('z-index' , '999999' );
		container.css('top' , position.top + 30 );
		container.show();

		
	});
	
	// this function courtesu of Werx Limited
	// http://werxltd.com/wp/2010/05/13/javascript-implementation-of-javas-string-hashcode-method/
	String.prototype.hashCode = function(){

    	var hash = 0;
    	if (this.length == 0) return hash;

    	for (i = 0; i < this.length; i++) {
        	char = this.charCodeAt(i);
        	hash = ((hash<<5)-hash)+char;
        	hash = hash & hash; // Convert to 32bit integer
    	}

    	return hash;
	}
	
});
