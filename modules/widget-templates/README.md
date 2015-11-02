# Sweet Widget Templates

Provides a simple templating system for WordPress Widgets that include 2 main features:

1. A Template Hierarchy for Widgets
2. Ability to select a custom template for individual widgets on the admin dashboard.

This plugin expects your theme to contain a folder named `widgets`. Place your widget templates into that folder. To change the name of this folder, see the `sweet_widgets_templates-folder` filter.

## Widget Template Hierarchy

When a widget is being rendered this plugin looks for an appropriate template in the active theme. If no appropriate template is found the widget will appear as it would normally, according to the details of `register_sidebar()` implementations.

## Custom Template Per Widget

When editing a Widget you can choose for a list of all templates found in the theme.

![Select widget template when editing](http://public.daggerhart.com/images/sweet-widgets/sweet-widgets-templates.jpg)

### Example use of the Sweet Widgets Template Hierarchy

Examples using a Sidebar with the ID of "sidebar-1", and a Widget with the ID of "text-6".

Description | Pattern | Example
---|---|---
Specific widget in specific sidebar. | `{sidebar-id}--{widget-id}` | `sidebar-1--text-6.php`
Any widget in a specific sidebar. | `{sidebar-id}--default` | `sidebar-1--default.php`
Specific widget in any sidebar. | `{widget-id}` | `text-6.php`
Any widget in any sidebar. | `widget--default` | `widget--default.php`

### Hooks

Type | Name | Description
---|---|---
filter | `sweet_widgets_templates-folder` | Change the folder where Widget templates are stored in the theme. Defaults to `widgets`. Signature: `( $folder )`
filter | `sweet_widgets_templates-replacements` | Template suggestion replacement pairs. Signature: `( $replacements, $instance, $widget, $args )`
filter | `sweet_widgets_templates-suggestions` | Provide or remove template suggestions. Signature: `( $suggestions, $instance, $widget, $args )`
filter | `sweet_widgets_templates-get_widget_data` | Modify data injected into the widget scope. All keys are automatically prefixed with `widget_` within the template. Signature: `( $data, $instance, $widget, $args )`

## Widget Template Template

Example widget template.

```php
<?php
/**
 * Available variables -- null if missing
 *
 * - $widget_title     | Title for widget
 * - $widget_content   | Content for widget
 * - $widget_instance  | $instance array of the widget
 * - $widget_object    | WP_Widget object
 * - $widget_args      | Sidebar parameters: (before_widget, before_title, etc...)
 * - $widget_id        | Unique id for the widget based on id_base and number
 * - $widget_classname | Widget object defined class name
 * 
 * Widget template hierarchy
 *
 * - {custom-template-name}.php    | If a widget has a specified template name in the Admin Dashboard, that template name takes priority.
 * - {sidebar-id}--{widget-id}.php | Specific widget in specific sidebar
 * - {sidebar-id}--default.php     | Any widget in specific sidebar
 * - {widget-id}.php               | Specific widget in any sidebar
 * - widget--default.php           | Default template for all widgets in all sidebars
 */
?>
<aside id="<?php echo esc_attr( $widget_id ); ?>" class="widget <?php echo esc_attr( $widget_classname ); ?>">
	<?php if ( $widget_title ) :?>
		<h2 class="widget-title"><?php echo $widget_title; ?></h2>
	<?php endif; ?>
	<?php if ( $widget_content ) : ?>
		<div class="widget-inner">
			<?php echo $widget_content; ?>
		</div>
	<?php endif; ?>
</aside>
```