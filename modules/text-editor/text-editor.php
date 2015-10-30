<?php

class Sweet_Widgets_Text_Editor {

	public $version = '0.0.1';
	
	/**
	 * Instantiate and hook plugin into WordPress
	 */
	static function register(){
		$plugin = new self();

		add_action( 'admin_enqueue_scripts', array( $plugin, 'admin_enqueue_scripts' ) );

		add_filter( 'widget_form_callback', array( $plugin, 'widget_form_callback' ), 999 , 2 );
		add_filter( 'widget_update_callback', array( $plugin, 'widget_update_callback' ), 999 , 4 );
	}

	/**
	 * Hook: admin_enqueue_script
	 */
	function admin_enqueue_scripts(){
		if ( get_current_screen()->id == 'widgets' ){
			wp_enqueue_script( 'sweet-widgets-text-editor', plugins_url( 'text-editor.js', __FILE__ ), array( 'jquery', 'admin-widgets' ), $this->version, true );
		}
	}

	/**
	 * Short-circuit the WP_Widget_Text form so we can inject our editor
	 *
	 * @see WP_Widget::form_callback
	 *
	 * @param $instance
	 * @param $widget
	 *
	 * @return mixed
	 */
	function widget_form_callback( $instance, $widget ){
		if ( is_a( $widget, 'WP_Widget_Text' ) && false !== $instance ) {

			add_filter( 'wp_default_editor', function () {
				return 'tinymce';
			});

			// we need $return for the in_widget_form action
			$return = null;
			ob_start();
				$return = $widget->form( $instance );
			$form = ob_get_clean();

			$top = explode( '<textarea', $form );
			$bottom = explode( '/textarea>', $top[1] );
			$top = $top[0];
			$bottom = $bottom[1];

			ob_start()
				?><input type="hidden" name="<?php echo $widget->get_field_name('text'); ?>" value=""><?php
				wp_editor( stripslashes( $instance['text'] ), $this->make_editor_id( $widget ), array(
					'editor_class' => 'sweet-widgets-text-editor',
					'editor_height' => '320px',
				) );
			$editor = ob_get_clean();

			echo $top.$editor.$bottom;

			do_action_ref_array( 'in_widget_form', array( &$widget, &$return, $instance ) );
			$instance = false;
		}
		return $instance;
	}

	/**
	 * Save our custom widget data
	 *
	 * Filter: widget_update_callback
	 *
	 * @see WP_Widget::update_callback
	 *
	 * @param $instance
	 * @param $new_instance
	 * @param $old_instance
	 * @param $widget
	 *
	 * @return mixed
	 */
	function widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		if ( is_a( $widget, 'WP_Widget_Text' ) ){
			if ( isset( $_POST[ $this->make_editor_id( $widget ) ] ) ){
				$text = $_POST[ $this->make_editor_id( $widget ) ];

				if ( current_user_can('unfiltered_html') ){
					$instance['text'] =  $text;
				}
				else {
					$instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $text ) ) ); // wp_filter_post_kses() expects slashed
				}
			}
		}
		
		return $instance;
	}

	/**
	 * Util: simple reusable editor id
	 * 
	 * @param $widget
	 *
	 * @return string
	 */
	function make_editor_id( $widget ){
		return "widget_{$widget->id_base}_{$widget->number}_sweet_widget_text_editor";
	}
}

Sweet_Widgets_Text_Editor::register();
