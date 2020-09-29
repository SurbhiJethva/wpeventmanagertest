(function( $ ) {


	$( document ).ready( function() {
		if ( $( '#description' ).length > 0 ) {
			wp.editor.initialize( 'description', {
				tinymce: {
					wpautop: true
				},
				quicktags: true
			} );
		}

        // WYSIWYG editor for textarea with class sab-editor.
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

	} );

})( jQuery );