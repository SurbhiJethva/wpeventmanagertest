(function( $ ) {

	$( document ).ready( function() {
		if ( $( '.user-description-wrap #description' ).length > 0 ) {
			wp.editor.initialize( 'description', {
				tinymce: {
					wpautop: true
				},
				quicktags: true
			} );
		}

        var sab_editor = jQuery('.sab-editor');
        if (sab_editor.length == 1) {
            var sab_editor_id = sab_editor.attr('id');
            wp.editor.initialize(sab_editor_id, {
                tinymce: {
                    wpautop: true,
                    browser_spellcheck: true,
                    mediaButtons: false,
                    wp_autoresize_on: true,
                    toolbar1: 'bold,italic,bullist,numlist,link,strikethrough',
                    setup: function (editor) {
                        editor.on('change', function () {
                            editor.save();
                            jQuery(sab_editor).trigger('change');
                        });
                    }
                },
                quicktags: true
            });

        } else if (sab_editor.length > 1) {
            sab_editor.each(function () {
                var sab_editor_id = jQuery(this).attr('id');
                wp.editor.initialize(sab_editor_id, {
                    tinymce: {
                        wpautop: true,
                        browser_spellcheck: true,
                        mediaButtons: false,
                        wp_autoresize_on: true,
                        toolbar1: 'bold,italic,link,strikethrough',
                        setup: function (editor) {
                            editor.on('change', function () {
                                editor.save();
                                jQuery(this).trigger('change');
                            });
                        }
                    },
                    quicktags: true
                });
            });

        }
		
		$( '#social_link_table' ).on( 'click', '.remove_links', function() {
			var socialremove = $( this ).parents( 'tr' );
			socialremove.remove();			
		} );
		
				
		$( ".social_link_add_btn a" ).click(function( e ) {
			e.preventDefault();
			var socialselectbox = {};
			var myArray = {'facebook':'Facebook','whatsapp':'WhatsApp','instagram':'Instagram','linkedin':'Linkedin','pinterest':'Pinterest','skype':'Skype','twitter':'Twitter','yahoo':'Yahoo','youtube':'Youtube','telegram':'Telegram','phone':'Phone'};
			socialselectbox.html = '<tr class="social_link_content"><th><select name="social_profile_list_id[]"><option value="">Select</option>';
			for (var key in myArray) {
			  socialselectbox.html = socialselectbox.html + '<option value="'+ key +'">'+ myArray[key] +'</option>';
			}
			socialselectbox.html = socialselectbox.html + '</select></th><td><input name="social_profile_link_data[]" type="text" class="regular-text" value=""><span class="remove_links dashicons dashicons-trash" style="cursor: pointer;"></span><td></tr>';
			$( '#social_link_table' ).append( socialselectbox.html );
		});
		

	} );

})( jQuery );