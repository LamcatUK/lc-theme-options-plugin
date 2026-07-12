# LC Theme Options WordPress Plugin

A WordPress plugin that provides granular control over theme and blog functionality, allowing administrators to disable blog, comments, gravatars, tags, emojis, and more through a simple admin interface.

## Features

- **Disable Blog**: Completely removes blog functionality including:
  - Hides Posts menu from admin
  - Prevents access to post-related admin pages (allows trash/delete actions)
  - Disables post type support
  - Removes blog-related dashboard widgets
  - Removes New Post button from admin bar
  - Automatically disables comments, gravatars, and tags

- **Disable Comments**: Removes comment functionality including:
  - Closes comments on all posts
  - Hides existing comments
  - Removes Comments menu from admin
  - Removes comment-related dashboard widgets
  - Hides discussion settings page
  - Removes Discussion metabox from classic and block editors
  - Removes Comments column from post/page list tables
  - Removes REST API comment endpoints
  - Forces `comment_status` to closed in REST API responses

- **Disable Gravatars**: Disables avatar/gravatar functionality:
  - Turns off gravatar display
  - Removes avatar options from user profiles
  - Replaces gravatars with empty content

- **Disable Tags**: Removes tag functionality:
  - Unregisters `post_tag` taxonomy from posts
  - Hides tag UI across admin (menu, metabox, columns, tag cloud, quick edit)
  - Removes Tags metabox from post editor

- **Disable Emojis**: Removes WordPress emoji scripts and styles:
  - Removes emoji detection script from `<head>` and admin
  - Removes emoji stylesheets
  - Removes emoji from feeds, email, and RSS
  - Removes emoji plugin from TinyMCE
  - Disables emoji SVG URL

- **Suppress Object Cache Warning**: Hides the "persistent object cache" suggestion from Site Health (useful for small sites where object caching is unnecessary)

- **ACF Block Edit Mode**: Forces Advanced Custom Fields blocks to stay in edit mode in the block editor, including immediately after a new block is inserted

- **Editor Width Override**: Increases and centers the Gutenberg content column so themes do not need to carry their own editor-width CSS

- **Dashboard Widget**: Adds a branded Lamcat dashboard widget with a quick contact CTA

## Installation

1. Upload the `lc-theme-options` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Tools > LC Theme Options** to configure settings

## Usage

### Admin Interface

The plugin adds a "LC Theme Options" page under the WordPress admin **Tools** menu. The settings page contains six checkboxes:

1. **Disable Blog** — When checked, completely disables all blog functionality and automatically enables the comments, gravatars, and tags options
2. **Disable Comments** — When checked, disables all comment-related functionality
3. **Disable Gravatars** — When checked, disables gravatar/avatar display
4. **Disable Tags** — When checked, removes tag functionality from posts
5. **Disable Emojis** — When checked, removes WordPress emoji scripts and styles from frontend and admin
6. **Suppress Object Cache Warning** — When checked, hides the persistent object cache suggestion in Site Health

### Checkbox Behavior

- When "Disable Blog" is checked, "Disable Comments", "Disable Gravatars", and "Disable Tags" are automatically checked and cannot be unchecked
- Individual options can be controlled independently when blog is not disabled

## What Gets Disabled

### When Blog is Disabled:
- Posts menu removed from admin
- All post-related admin pages inaccessible (except trash/delete actions)
- Post type support disabled
- Blog-related dashboard widgets removed
- New Post button removed from admin bar
- Comments, gravatars, and tags disabled (inherited)

### When Comments are Disabled:
- Comments closed on all posts
- Existing comments hidden
- Comments menu removed from admin
- Comment-related dashboard widgets removed
- Discussion settings page inaccessible
- Discussion metabox removed from post/page editors
- Comments section removed from admin bar
- Comments column removed from post/page list tables
- REST API comment endpoints removed
- Comment status forced to closed in REST responses

### When Gravatars are Disabled:
- Avatar display turned off
- Gravatar images replaced with empty content
- Avatar options removed from user profiles

### When Tags are Disabled:
- Post tag taxonomy unregistered from posts
- Tag UI hidden everywhere (menu, metabox, columns, tag cloud, quick edit)
- Tags metabox removed from post editor

### When Emojis are Disabled:
- Emoji detection script removed from frontend and admin
- Emoji stylesheets removed
- Emoji removed from feeds, email, and RSS
- Emoji plugin removed from TinyMCE
- Emoji SVG URL disabled

## Technical Details

- **Version**: 1.2.1
- **Requires**: WordPress 4.0+
- **Requires PHP**: 5.6+
- **License**: GPL v2 or later
- **Optional**: Advanced Custom Fields (for ACF block edit mode feature)

## Dependencies

- **WordPress**: 4.0+ (some features, such as REST API comment removal and block editor discussion panel removal, require newer versions)
- **Advanced Custom Fields** (optional) — ACF block edit mode enforcement only takes effect when ACF is active

## File Structure

```
lc-theme-options/
├── lc-theme-options.php    # Main plugin file
└── README.md               # This documentation
```

## Hooks and Filters Used

The plugin uses various WordPress hooks and filters to achieve its functionality:

- `admin_menu` — For adding/removing menu items
- `admin_init` — For settings registration and redirects
- `init` — For disabling post type support, unregistering tags, and disabling emojis
- `admin_head` — For hiding discussion settings and block editor discussion panel
- `wp_dashboard_setup` — For removing dashboard widgets and adding custom widget
- `admin_bar_menu` — For modifying admin bar
- `add_meta_boxes` — For removing discussion and tags metaboxes
- `enqueue_block_editor_assets` — For ACF block edit mode enforcement
- `enqueue_block_editor_assets` — For ACF block edit mode enforcement and editor width styling
- `plugins_loaded` — For early emoji cleanup
- `comments_open` / `pings_open` — For disabling comments
- `comments_array` — For hiding existing comments
- `get_avatar` — For disabling gravatars
- `rest_endpoints` — For removing comment REST API endpoints
- `rest_prepare_post` / `rest_prepare_page` — For forcing comment status in REST responses
- `manage_posts_columns` / `manage_pages_columns` — For removing comments column
- `site_status_should_suggest_persistent_object_cache` — For suppressing object cache warning
- `tiny_mce_plugins` — For removing emoji from TinyMCE
- `emoji_svg_url` — For disabling emoji SVG
- `plugin_action_links_lc-theme-options/lc-theme-options.php` — For settings link on plugins page

## Support

This plugin is provided as-is. For customizations or support, please contact the plugin author.

## Changelog

### 1.2.2
- Added a Gutenberg editor width override so the main content column is centered and widened without requiring theme CSS

### 1.2.1
- Upgraded the dashboard widget to use Lamcat branding, the bundled image asset, and a direct contact button

### 1.1.0
- Added disable tags functionality
- Added disable emojis functionality (removes emoji scripts/styles from frontend and admin)
- Added suppress object cache warning option for Site Health
- Added ACF block edit mode enforcement via inline JavaScript
- Added LC Theme Options dashboard widget
- Added plugin settings link on Installed Plugins page
- Improved blog redirect logic (supports custom post types and trash actions)
- Safer tag removal (hides taxonomy UI without destroying the taxonomy)
- Fixed admin-only initialization so frontend filters (comments, avatars) work correctly
- Combined REST API comment removal and discussion panel cleanup

### 1.0.0
- Initial release
- Added blog disable functionality
- Added comments disable functionality
- Added gravatars disable functionality
- Added admin interface under Tools menu
