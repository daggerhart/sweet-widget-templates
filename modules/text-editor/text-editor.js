(function($){

	/**
	 * TinyMCE on widget pages takes a bit of work
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
			
			// reload edtiors after sorting
			$( '.widgets-sortables' ).on( 'sortstop', function( event, obj ) {
				_this.reloadEditors();
			} );

			// watch for an ajax call so we can reload editors after saving a text widget
			$( document ).ajaxComplete(function( event, xhr, settings ) {
				var data = _this.decodeUriParams( settings.data );
				if ( data && data.action && data.action == 'save-widget' && data.id_base == 'text' ) {
					_this.reloadEditors();
				}
			});

			// initial reload of the editors so we can bind events
			_this.firstLoadEditors();
		},

		/**
		 * Loop through editors, remove them, modify the originals with events,
		 * and re-instantiate them.
		 * 
		 * @link http://www.tinymce.com/wiki.php/api4:event.tinymce.Editor.blur
		 */
		firstLoadEditors: function(){
			setTimeout( function(){
				$( '.sweet-widgets-text-editor' ).each( function() {
					// remove the editors
					var editor = tinymce.get( this.id );
					tinymce.remove( editor );
	
					tinyMCEPreInit.mceInit[ this.id ].setup = function(editor){
						// subscribe to the blur event for saving the document
						editor.on( 'blur', function ( e ) {
							editor.save();
						} );
					};
	
					tinymce.init( tinyMCEPreInit.mceInit[ this.id ] );
				});
			}, 1 );
		},

		/**
		 * Loop through editors, save content, then reload them.
		 */
		reloadEditors: function() {
			$( '.sweet-widgets-text-editor' ).each( function() {
				var editor = tinymce.get( this.id );

				if ( ! editor.isHidden() ) {
					editor.save();
				}
				
				tinymce.remove( editor );
				tinymce.init( tinyMCEPreInit.mceInit[ this.id ] );
			});
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