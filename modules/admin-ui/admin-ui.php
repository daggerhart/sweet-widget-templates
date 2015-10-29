<?php

class Sweet_Widgets_Admin_UI {

	public $version = '0.0.1';
	
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
		if ( get_current_screen()->id == 'widgets' ){
			wp_enqueue_script( 'sweet-widgets-admin-ui', plugins_url( 'admin-ui.js', __FILE__ ), array( 'jquery', 'admin-widgets' ), $this->version );
			wp_enqueue_style( 'sweet-widgets-admin-ui', plugins_url( 'admin-ui.css', __FILE__ ), array(), $this->version );
		}
	}
}

Sweet_Widgets_Admin_UI::register();