<?php
/**
 * Available variables -- null if missing
 *
 * $widget_title - Title for widget
 * $widget_content - Content for widget
 * $widget_instance - $instance array of the widget
 * $widget_object - WP_Widget object
 * $widget_args - Sidebar parameters: (before_widget, before_title, etc...)
 * $widget_id - Unique id for the widget based on id_base and number
 * $widget_classname - Widget object defined class name
 */
?>
<aside id="<?php echo esc_attr( $widget_args['widget_id'] ); ?>" class="widget <?php echo esc_attr(); ?>">
	<?php if ( $widget_title ) :?>here
		<h2 class="widget-title"><?php echo $widget_title; ?></h2>
	<?php endif; ?>
	<?php if ( $widget_content ) : ?>
		<div class="widget-inner">
			<?php echo $widget_content; ?>
		</div>
	<?php endif; ?>
</aside>