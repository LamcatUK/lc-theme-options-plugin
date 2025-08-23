# LC Theme Options WordPress Plugin

A WordPress plugin that provides granular control over blog functionality, allowing administrators to disable blog features, comments, and gravatars through a simple admin interface.

## Features

- **Disable Blog**: Completely removes blog functionality including:
  - Hides Posts menu from admin
  - Prevents access to post-related admin pages
  - Removes blog-related dashboard widgets
  - Disables post type support
  - Automatically disables comments and gravatars

- **Disable Comments**: Removes comment functionality including:
  - Closes comments on all posts
  - Hides existing comments
  - Removes Comments menu from admin
  - Removes comment-related dashboard widgets
  - Hides discussion settings page

- **Disable Gravatars**: Disables avatar/gravatar functionality:
  - Turns off gravatar display
  - Removes avatar options from user profiles

## Installation

1. Upload the `lc-theme-options` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Tools > LC Theme Options to configure settings

## Usage

### Admin Interface

The plugin adds a "LC Theme Options" page under the WordPress admin Tools menu. This page contains three checkboxes:

1. **Disable Blog** - When checked, completely disables all blog functionality and automatically enables options 2 and 3
2. **Disable Comments** - When checked, disables all comment-related functionality
3. **Disable Gravatars** - When checked, disables gravatar/avatar display

### Checkbox Behavior

- When "Disable Blog" is checked, "Disable Comments" and "Disable Gravatars" are automatically checked and cannot be unchecked
- Individual comment and gravatar options can be controlled independently when blog is not disabled

## What Gets Disabled

### When Blog is Disabled:
- Posts menu is removed from admin
- All post-related admin pages are inaccessible
- Post type support is disabled
- Blog-related dashboard widgets are removed
- New Post button is removed from admin bar
- All comment functionality (inherited)
- All gravatar functionality (inherited)

### When Comments are Disabled:
- Comments are closed on all posts
- Existing comments are hidden
- Comments menu is removed from admin
- Comment-related dashboard widgets are removed
- Discussion settings page is inaccessible
- Comments section is removed from admin bar

### When Gravatars are Disabled:
- Avatar display is turned off
- Gravatar images are replaced with empty content
- Avatar options are removed from user profiles

## Technical Details

- **Version**: 1.0.0
- **Requires**: WordPress 4.0+
- **PHP**: 5.6+
- **License**: GPL v2 or later

## File Structure

```
lc-theme-options/
├── lc-theme-options.php    # Main plugin file
└── README.md             # This documentation
```

## Hooks and Filters Used

The plugin uses various WordPress hooks and filters to achieve its functionality:

- `admin_menu` - For adding/removing menu items
- `admin_init` - For settings registration and redirects
- `init` - For disabling post type support
- `wp_dashboard_setup` - For removing dashboard widgets
- `admin_bar_menu` - For modifying admin bar
- `comments_open` / `pings_open` - For disabling comments
- `get_avatar` - For disabling gravatars

## Support

This plugin is provided as-is. For customizations or support, please contact the plugin author.

## Changelog

### 1.0.0
- Initial release
- Added blog disable functionality
- Added comments disable functionality  
- Added gravatars disable functionality
- Added admin interface under Tools menu
