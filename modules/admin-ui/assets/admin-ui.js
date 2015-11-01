(function($){

	/**
	 * Overlay creates a backdrop and supports the CSS in appearing like a modal
	 */
	var Sweet_Widgets_Admin_UI_Overlay = {
		overlay: null,
		widget: null,
		top_offset: 50,

		/**
		 * Create the overlay element and bind events
		 */
		init: function(){
			this.overlay = $('<div id="sweet-widgets-admin-ui-overlay">');
			$('#wpwrap' ).append( this.overlay );
			
			this.bindEvents();
		},

		/**
		 * Make use of the same events the slideToggle uses to show and hide our
		 * "modal" widget.
		 */
		bindEvents: function(){
			var _this = this;
			
			$(document.body).bind('click.widgets-toggle', function(e) {
				var target = $(e.target);
				
				// and it was clicked within the widget-top div
				if ( target.parents('.widget-top').length && ! target.parents('#available-widgets').length ) {
					_this.widget = target.closest('.widget');
					
					// toggle the overlay
					if ( _this.overlay.hasClass('visible') ){
						_this.hideOverlay();
					}
					else {
						_this.showOverlay();
						_this.setWidgetTop();
					}
				}
				// or the remove button
				else if ( target.hasClass('widget-control-remove') ) {
					_this.hideOverlay();
				}
				// or the close button
				else if ( target.hasClass('widget-control-close') ) {
					_this.hideOverlay();
				}
			});

			window.addEventListener('resize', function(e){
				_this.setWidgetTop();
			});
		},

		/**
		 * Make sure an open widgets is reset to the correct position 
		 * if window is resized.
		 */
		setWidgetTop: function(){
			if ( this.widget && this.widget.hasClass('open') ){
				this.widget.css( 'top', parseInt( window.pageYOffset + this.top_offset ) + 'px' );
			}
		},

		/**
		 * Make our overlay visible, and do any additional work needed to 
		 * control the position of the widget.
		 */
		showOverlay: function(){
			$('.widgets-sortables' ).addClass( 'sweet-widgets-breakout' );
			this.overlay.addClass('visible');
		},

		/**
		 * Hide the overlay and undo any shenanigans done during showOverlay().
		 */
		hideOverlay: function(){
			this.overlay.removeClass('visible');
			$('.widgets-sortables' ).removeClass( 'sweet-widgets-breakout' );
		}
	};
	
	$(document).ready(function() {
		Sweet_Widgets_Admin_UI_Overlay.init();
	});
	
})(jQuery);