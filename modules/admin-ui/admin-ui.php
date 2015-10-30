<?php

class Sweet_Widgets_Admin_UI {

	public $version = '0.0.2';
	
	/**
	 * Instantiate and hook plugin into WordPress
	 */
	static function register(){
		$plugin = new self();

		add_action( 'admin_enqueue_scripts', array( $plugin, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Hook: admin_enqueue_scripts
	 */
	function admin_enqueue_scripts(){
		$screen_id = get_current_screen()->id ;
		if ( $screen_id == 'widgets' ) {
			wp_enqueue_style( 'sweet-widgets-admin-ui', plugins_url( 'admin-ui.css', __FILE__ ), array(), $this->version );
			wp_enqueue_script( 'sweet-widgets-admin-ui', plugins_url( 'admin-ui.js', __FILE__ ), array( 'jquery', 'admin-widgets' ), $this->version );
		}
		if ( $screen_id == 'customize' ){
			wp_enqueue_style( 'sweet-widgets-admin-ui', plugins_url( 'admin-ui.css', __FILE__ ), array(), $this->version );
			wp_enqueue_script( 'sweet-widgets-admin-ui-customizer', plugins_url( 'admin-ui-customizer.js', __FILE__ ), array( 'jquery', 'underscore', 'customize-controls' ), $this->version );
		}
	}
}

Sweet_Widgets_Admin_UI::register();