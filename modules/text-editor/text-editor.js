(function($){

	/**
	 * TinyMCE on widget pages takes a bit of work.
     * We have to track save button clicks, resulting save ajax calls, and
     * re-sorts (drag and drop), as well as repair quicktag toolbars on every
     * reload.
	 *
	 * @link https://core.trac.wordpress.org/ticket/19173
	 */
	var Sweet_Widgets_Text_Editor = {
		init: function(){
			this.bindEvents();
		},

		/**
		 * Subscribe to some events for keeping tinymce in line
		 */
		bindEvents: function(){
			var _this = this;

            // before widget saves, save editor content to the textarea
            $('input[id^=widget-text-].widget-control-save' ).on('click', function(event){
                var editor_id = $( event.target ).closest( '.widget' ).find( 'textarea.wp-editor-area' ).attr('id');

                _this.saveEditor( editor_id );
                _this.reloadEditor( editor_id );
            });

            // watch for ajax call so we can reload editors after saving
            $( document ).ajaxComplete( function( event, xhr, settings ) {
                var data = _this.decodeUriParams( settings.data );
                if ( data && data.action && data.action == 'save-widget' && data.id_base == 'text' ) {
                    var editor_id = _this.makeEditorId( data );

                    _this.reloadEditor( editor_id );
                }
            });

			// reload editor after sorting
			$( '.widgets-sortables' ).on( 'sortstop', function( event, obj ) {
                var $widget = $( obj.item[0] );
                var id_base = $widget.find('input[name=id_base]' ).val();

                if ( id_base == 'text' ) {
                    var editor_id = $widget.find( 'textarea.wp-editor-area' ).attr('id');

                    _this.reloadEditor( editor_id );
                }
			});
		},

        /**
         * Save the contents of a tinymce editor
         *
         * @param editor_id
         */
        saveEditor: function( editor_id ){
            var editor = tinymce.get( editor_id );

            if ( ! editor ) {
                return;
            }

            if ( ! editor.isHidden() ) {
                editor.save();
            }
        },

        /**
         * Remove and re-init a tinymce editor
         *
         * @param editor_id
         */
        reloadEditor: function( editor_id ){
            var editor = tinymce.get( editor_id );

            if ( ! editor ) {
                return;
            }

            tinymce.remove( editor );
            tinymce.init( tinyMCEPreInit.mceInit[ editor_id ] );

            this.reloadQuicktags( editor_id, getUserSetting( 'editor' ) == 'html' );
        },

        /**
         * Remove and re-create the quicktags bar for this editor
         *
         * @param editor_id
         * @param editor_hidden
         */
        reloadQuicktags: function( editor_id, editor_hidden ){
            var $wrapper = $( '#wp-' + editor_id + '-wrap' );

            $wrapper.find( '.quicktags-toolbar' ).remove();
            $wrapper.unbind( 'onmousedown' ).bind( 'onmousedown', function(){
                window.wpActiveEditor = editor_id;
            });

            if ( editor_hidden ) {
                window.wpActiveEditor = editor_id;
            }

            //Add settings with current widget id into QTags
            QTags({id: editor_id});

            //Re-init the QTags
            QTags._buttonsInit();

            // if the user was on the html editor, return to it
            if ( editor_hidden ){
                $wrapper.find('.wp-switch-editor.switch-html' ).trigger('click');
            }
        },

        /**
         * Util: Construct an editor id the same way as it is constructed in PHP
         *
         * @param widget
         * @returns {string}
         */
        makeEditorId: function( widget ){
            return "widget_" + widget.id_base + "_" + widget.widget_number + "_sweet_widget_text_editor";
        },

		/**
		 * Util: convert a url of query parameters into an object
		 * 
		 * @param string
		 * @returns {{}}
		 */
		decodeUriParams: function ( string ){
			var obj = {};
			string.replace(/([^=&]+)=([^&]*)/g, function(m, key, value) {
				obj[decodeURIComponent(key)] = decodeURIComponent(value);
			});
			return obj;
		}

	};
	
	$(document).ready(function() {
		Sweet_Widgets_Text_Editor.init();
	});
	
})(jQuery);