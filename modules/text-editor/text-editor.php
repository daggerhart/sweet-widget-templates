<?php

class Sweet_Widgets_Text_Editor {

	public $version = '1.0.0';
	
	/**
	 * Instantiate and hook plugin into WordPress
	 */
	static function register(){
		$plugin = new self();

		add_action( 'admin_enqueue_scripts', array( $plugin, 'admin_enqueue_scripts' ) );
		add_action( 'in_widget_form', array( $plugin, 'in_widget_form' ), 20, 3 );
		add_filter( 'widget_update_callback', array( $plugin, 'widget_update_callback' ), 999 , 4 );
	}

	/**
	 * Hook: admin_enqueue_script
	 */
	function admin_enqueue_scripts(){
		if ( get_current_screen()->id == 'widgets' ){
			wp_enqueue_script( 'sweet-widgets-text-editor', plugins_url( 'text-editor.js', __FILE__ ), array( 'jquery', 'admin-widgets' ), $this->version, true );
			wp_enqueue_style( 'sweet-widgets-text-editor', plugins_url( 'text-editor.css', __FILE__ ), array(), $this->version );
			
			// only modify sidebar params when on the widget edit page
			add_filter( 'dynamic_sidebar_params', array( $this, 'dynamic_sidebar_params' ), 999 );
		}
	}

	/**
	 * Override some admin widget params to inject our own classes for styling
	 * 
	 * @param $params
	 *
	 * @return mixed
	 */
	function dynamic_sidebar_params( $params ){
		// double-check we're on the admin page
		if ( $params[0]['before_title'] == '%BEG_OF_TITLE%' ) {
			$old = "class='widget'";
			$new = "class='widget widget-name-{$params[0]['widget_name']}'";
			$params[0]['before_widget'] = str_replace( $old, $new, $params[0]['before_widget'] );
		}
		return $params;
	}

	/**
	 * Allow user to specify a template per widget
	 *
	 * Action: in_widget_form
	 * @link  https://codex.wordpress.org/Function_Reference/wp_editor
	 * 
	 * @param $widget
	 * @param $return
	 * @param $instance
	 */
	function in_widget_form( &$widget, &$return, $instance ){
		if ( is_a( $widget, 'WP_Widget_Text' ) ){
			wp_editor( $instance['text'], $this->make_editor_id( $widget ), array(
				'editor_class' => 'sweet-widgets-text-editor',
				'editor_height' => '320px',
			) );
		}
	}

	/**
	 * Save our custom widget data
	 *
	 * Filter: widget_update_callback
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
				$instance['text'] = wp_kses_post( $_POST[ $this->make_editor_id( $widget ) ] );
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
