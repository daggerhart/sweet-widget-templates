# Sweet Widget Templates

Provides a simple templating system for WordPress Widgets that include 2 main features:

1. A Template Hierarchy for Widgets
2. Ability to select a custom template for individual widgets on the admin dashboard.

## Widget Template Hierarchy

When a widget is being rendered this plugin looks for an appropriate template in the active theme. If no appropriate template is found the widget will appear as it would normally, according to the details of `register_sidebar()` implementations.

## Custom Template Per Widget

When editing a Widget you can choose for a list of all templates found in the theme.

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
filter | `sweet_widgets_templates-folder` | Change the folder where Widget templates are stored in the theme. Defaults to `widgets`.
filter | `sweet_widgets_templates-replacements` | Template suggestion replacement pairs. Signature: `( $replacements, $instance, $widget, $args )`
filter | `sweet_widgets_templates-suggestions` | Provide or remove template suggestions. Signature: `( $suggestions, $instance, $widget, $args )`
