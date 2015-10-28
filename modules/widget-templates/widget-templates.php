<?php
/**
 * Class Sweet_Widgets_Templates
 *
 * Filters:
 *      - sweet_widgets_templates-suggestions
 *      - sweet_widgets_templates-folder
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
		$plugin->folder = apply_filters( 'sweet_widgets_templates-folder', $plugin->folder );

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
		$sweet_widgets_template = isset( $instance['sweet_widgets_template'] ) ? esc_attr( $instance['sweet_widgets_template'] ) : '';
		?>
		<div class="sweet-widget-templates">
			<p>
				<label for="<?php echo $widget->get_field_id( 'sweet_widgets_template' ); ?>">
					<strong><?php _e( 'Sweet Widget Template' ); ?></strong>
				</label>
				<select
					id="<?php echo $widget->get_field_id( 'sweet_widgets_template' ); ?>"
					class="widefat"
					name="<?php echo $widget->get_field_name( 'sweet_widgets_template' ); ?>">
					<?php foreach( $this->get_widget_template_options() as $value => $text ) : ?>
						<option 
							value="<?php echo esc_attr( $value ); ?>" 
							<?php selected( $value, $sweet_widgets_template ); ?>>
							<?php echo esc_attr( $text ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php $this->get_widget_templates(); ?>
			</p>
			<p class="help">
				<?php _e( 'Select the preferred template for this widget. If "Default" is selected, the widget will use the next available template in the hierarchy.' ); ?>
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
		$instance['sweet_widgets_template'] = isset( $new_instance['sweet_widgets_template'] ) ? $new_instance['sweet_widgets_template'] : '';

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
		if ( isset( $instance['sweet_widgets_template'] ) && ! empty( $instance['sweet_widgets_template'] ) ) {
			$templates[] = esc_attr( $instance['sweet_widgets_template'] );
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
		$templates = apply_filters( 'sweet_widgets_templates-suggestions', $templates, $instance, $widget, $args );

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

	/**
	 * Util: Get an array of all templates in the current theme's {folder}.
	 *
	 * @return array
	 */
	function get_widget_templates(){
		$templates = array();
		$path = get_stylesheet_directory() . '/' . $this->folder . '/*.php';
		foreach( glob( $path ) as $file ) {
			$templates[] = $file;
		}
		return $templates;
	}

	/**
	 * Util: Create an array of options from the exist widget templates
	 *
	 * @return array
	 */
	function get_widget_template_options(){
		$options = array(
			'' => __( '- Default -' )
		);
		$templates = $this->get_widget_templates();

		foreach ( $templates as $file ){
			$name = basename( $file );
			$options[ $name ] = $name;
		}

		return $options;
	}
}

Sweet_Widgets_Templates::register();