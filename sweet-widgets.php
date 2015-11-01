<?php
/*
 * Plugin Name: Sweet Widgets
 * Plugin URI: http://github.com/daggerhart/
 * Description: A suite of Widget related plugins for developers.
 * Author: Jonathan Daggerhart
 * Version: 0.1
 * Author URI: http://www.daggerhart.com
*/

class Sweet_Widgets {

	/**
	 * Instantiate and hook the plugin into WordPress
	 */
	static function register(){
		$plugin = new self();

		$plugin->load_enabled_modules();
	}

	/**
	 * Get an array of plugin settings
	 *
	 * @return array
	 */
	function get_settings(){
		$option_values = get_option( 'sweet_widgets_settings', array() );

		$settings = array_replace(
			array(
				'enabled_modules' => array(
					'widget-templates' => 1,
					'admin-ui' => 1,
				),
			),
			$option_values
		);

		return $settings;
	}

	/**
	 * Util: Auto load modules that are enabled
	 */
	function load_enabled_modules(){
		$settings = $this->get_settings();
		$module_files = $this->get_modules_files();

		foreach ( $module_files as $module_name => $module_file ){
			if ( isset( $settings['enabled_modules'][ $module_name ] ) ) {
				require_once( $module_file );
			}
		}
	}

	/**
	 * Util: get an array of module names and their files
	 *
	 * @return array
	 */
	function get_modules_files(){
		$list = array();
		foreach( glob( __DIR__ . '/modules/*' , GLOB_ONLYDIR ) as $dir ){
			$module_name = basename( $dir );
			$module_filename = $module_name . '.php';
			$module_file = $dir . '/' . $module_filename;

			if ( file_exists( $module_file ) ){
				$list[ $module_name ] = $module_file;
			}
		}

		return $list;
	}
}

Sweet_Widgets::register();

