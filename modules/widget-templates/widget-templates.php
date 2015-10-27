<?php
/**
 * Class Sweet_Widgets_Templates
 *
 * Filters:
 *      - sweet_widget_templates-suggestions
 *      - sweet_widget_templates-folder
 */
class Sweet_Widgets_Templates {

	// subdirectory in theme where widget templates are kept
	public $folder = 'widgets';

	public $cache = array();

	/**
	 * Instantiate and hook plugin into WordPress
	 */
	static function register(){
		$plugin = new self();
		$plugin->folder = apply_filters( 'sweet_widget_templates-folder', $plugin->folder );

		add_action( 'in_widget_form', array( $plugin, 'in_widget_form' ), 20, 3 );
		add_filter( 'widget_update_callback', array( $plugin, 'widget_update_callback' ), 20 , 4 );
		add_filter( 'widget_display_callback', array( $plugin, 'widget_display_callback' ), 20, 3 );
	}

	/**
	 * Allow user to specify a template per widget
	 *
	 * Action: in_widget_form
	 *
	 * @param $widget
	 * @param $return
	 * @param $instance
	 */
	function in_widget_form( &$widget, &$return, $instance ){
		$sweet_template_enabled = isset( $instance['sweet_template_enabled'] ) ? esc_attr( $instance['sweet_template_enabled'] ) : 0;
		$sweet_template = isset( $instance['sweet_template'] ) ? esc_attr( $instance['sweet_template'] ) : '';
		?>

		<h4>Sweet Widget Templates</h4>
		<div class="sweet-widget-templates">
			<p>
				<label for="<?php echo $widget->get_field_id( 'sweet_template_enabled' ); ?>">
					<input
						type="hidden"
						name="<?php echo $widget->get_field_name( 'sweet_template_enabled' ); ?>"
						value="0">
					<input
						type="checkbox"
						id="<?php echo $widget->get_field_id( 'sweet_template_enabled' ); ?>"
						name="<?php echo $widget->get_field_name( 'sweet_template_enabled' ); ?>"
						<?php checked( $sweet_template_enabled, 1 ); ?>
						value="1">
					<strong><?php _e( 'Override template' ); ?></strong>
				</label>
			</p>
			<p class="help"><?php _e( '' ); ?></p>

			<p>
				<label for="<?php echo $widget->get_field_id( 'sweet_template' ); ?>">
					<strong><?php _e( 'Template name' ); ?></strong>
				</label>
				<input
					type="text"
					id="<?php echo $widget->get_field_id( 'sweet_template' ); ?>"
					class="widefat"
					name="<?php echo $widget->get_field_name( 'sweet_template' ); ?>"
					value="<?php echo $sweet_template; ?>">
			</p>
			<p class="help">
				<?php 
					printf( __( 'Provide a file name, without extension, that exists in the %s folder within your theme.  Example: %s to load a file found at %s.' ),
						"<code>{$this->folder}</code>",
						'<code>my-template</code>',
						"<code>[your-theme]/{$this->folder}/my-template.php</code>"
					); ?>
			</p>
		</div>
		<?php
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
		$instance['sweet_template_enabled'] = isset( $new_instance['sweet_template_enabled'] ) ? $new_instance['sweet_template_enabled'] : 0;
		$instance['sweet_template'] = isset( $new_instance['sweet_template'] ) ? $new_instance['sweet_template'] : '';

		return $instance;
	}

	/**
	 * Look for templates and hijack the widget display process if found.
	 *
	 * Filter: widget_display_callback
	 *
	 * @see WP_Widget::display_callback()
	 *
	 * @param $instance
	 * @param $widget
	 * @param $args
	 *
	 * @return mixed
	 */
	function widget_display_callback( $instance, $widget, $args ){
		// clean up any previous widget data stored in the query_vars
		$this->reset_query_vars();

		// make a list of template suggestions
		$templates = $this->get_template_suggestions( $instance, $widget, $args );

		// look for suggested templates, and handle the first one found
		$found = locate_template( $templates );

		if ( $found ){
			// We have a custom template, now we need to setup some data for
			// the template to use, then load it.
			$this->setup_widget_template_data( $instance, $widget, $args );

			// execute the widget
			load_template( $found, false );

			// set instance to false to short circuit the normal process
			$instance = false;
		}
		else { echo "TEMPLATE NOT FOUND"; }

		return $instance;
	}

	/**
	 * Util: Build an array of possible template suggestions
	 *
	 * - {sidebar-id}--{widget-id}.php
	 *      Specific widget in specific sidebar
	 *
	 * - {sidebar-id}.php
	 *      Any widget in specific sidebar
	 *
	 * - {widget-id}.php
	 *      Specific widget in any sidebar
	 *
	 * @param $instance
	 * @param $widget
	 * @param $args
	 *
	 * @return array
	 */
	function get_template_suggestions( $instance, $widget, $args ){
		$templates = array();

		// if the widget has specified a template, look for it first.
		if ( isset( $instance['sweet_template_enabled'], $instance['sweet_template'] ) &&
		     $instance['sweet_template_enabled'] == 1 )
		{
			$templates[] = esc_attr( $instance['sweet_template'] );
		}

		// common suggestions
		$templates[] = "{$args['id']}--{$widget->id}";
		$templates[] = "{$args['id']}--default";
		$templates[] = "{$widget->id}";
		$templates[] = "widget--default";

		// prepare templates
		foreach( $templates as &$template ){
			$template = "{$this->folder}/{$template}.php";
		}

		// allow for alterations
		$templates = apply_filters( 'sweet_widget_templates-suggestions', $templates, $instance, $widget, $args );

		return $templates;
	}

	/**
	 * Util: Execute the widget and set its data as values of the global
	 * $wp_query->query_vars array.
	 *
	 * @param $instance
	 * @param $widget
	 * @param $args
	 */
	function setup_widget_template_data( $instance, $widget, $args ){
		$original_args = $args;

		// alter the parameters
		$args = array_replace( $args, array(
			'before_widget' => '',
			'before_title' => '',
			'after_title' => '<!--sweet-widget-templates-break-->',
			'after_widget' => '',
		) );

		// execute the widget and capture its output into separate 'title'
		// and 'content' data
		ob_start();
			$this->override_widget_display( $instance, $widget, $args );
		$document = ob_get_clean();
		$document = explode( '<!--sweet-widget-templates-break-->', $document );

		// default values to null
		$widget_title = $widget_content = null;

		if ( count( $document ) === 1 ){
			// there is only content, no title
			$widget_content = $document[0];
		}
		else {
			$widget_title = $document[0];
			$widget_content = $document[1];
		}

		// add our data to the query vars for later extraction into scope of loaded template
		set_query_var( 'widget_title', $widget_title );
		set_query_var( 'widget_content', $widget_content );
		set_query_var( 'widget_instance', $instance );
		set_query_var( 'widget_object', $widget );
		set_query_var( 'widget_args', $original_args );
		set_query_var( 'widget_id', $widget->id );
		set_query_var( 'widget_classname', $widget->widget_options['classname'] );
	}

	/**
	 * Util: Reset the query vars this plugin uses so they don't leak
	 */
	function reset_query_vars(){
		// add our data to the query vars for later extraction into scope of loaded template
		set_query_var( 'widget_title', null );
		set_query_var( 'widget_content', null );
		set_query_var( 'widget_instance', null );
		set_query_var( 'widget_object', null );
		set_query_var( 'widget_args', null );
		set_query_var( 'widget_id', null );
		set_query_var( 'widget_classname', null );
	}

	/**
	 * Util: Execute the widget as would normally happen had we not
	 * short-circuited the default process.
	 *
	 * @see WP_Widget::display_callback()
	 *
	 * @param $instance
	 * @param $widget
	 * @param $args
	 */
	function override_widget_display( $instance, $widget, $args ){
		$was_cache_addition_suspended = wp_suspend_cache_addition();
		if ( $widget->is_preview() && ! $was_cache_addition_suspended ) {
			wp_suspend_cache_addition( true );
		}

		$widget->widget( $args, $instance );

		if ( $widget->is_preview() ) {
			wp_suspend_cache_addition( $was_cache_addition_suspended );
		}
	}
}

Sweet_Widgets_Templates::register();