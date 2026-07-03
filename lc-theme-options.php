<?php
/**
 * Plugin Name: LC Theme Options
 * Plugin URI: https://github.com/LamcatUK/lc-theme-options
 * Description: A WordPress plugin to manage theme options including disabling blog, comments, gravatars, tags, emojis, and more.
 * Version: 1.2.0
 * Author: Lamcat - DS
 * License: GPL v2 or later
 *
 * @package LC_Theme_Options
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
if ( ! defined( 'LC_THEME_OPTIONS_VERSION' ) ) {
	define( 'LC_THEME_OPTIONS_VERSION', '1.2.0' );
}
if ( ! defined( 'LC_THEME_OPTIONS_PLUGIN_DIR' ) ) {
	define( 'LC_THEME_OPTIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'LC_THEME_OPTIONS_PLUGIN_URL' ) ) {
	define( 'LC_THEME_OPTIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! class_exists( 'LCThemeOptions' ) ) {
	/**
	 * Class LCThemeOptions
	 *
	 * Handles the LC Theme Options plugin functionality including disabling blog, comments, gravatars, tags, emojis, and more.
	 *
	 * @package LC_Theme_Options
	 */
	class LCThemeOptions {

		/**
		 * Define the option name for storing plugin settings.
		 *
		 * @var string Option name for storing plugin settings.
		 */
		private $option_name = 'lc_theme_options';

		/**
		 * Activate the plugin.
		 *
		 * Activates the plugin and sets default options when the plugin is activated.
		 *
		 * Plugin activation callback.
		 *
		 * @return void
		 */
		public static function activate() {
			$default_options = array(
				'disable_blog'                => 0,
				'disable_comments'            => 1,
				'disable_gravatars'           => 1,
				'disable_tags'                => 0,
				'disable_emojis'              => 0,
				'suppress_object_cache_warning' => 0,
			);
			add_option( 'lc_theme_options', $default_options );
		}

		/**
		 * Plugin deactivation callback.
		 *
		 * @return void
		 */
		public static function deactivate() {
		}

		/**
		 * Constructor: initialize globally so frontend filters work.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );

			if ( is_admin() ) {
				add_action( 'init', array( $this, 'remove_block_editor_discussion_panel' ), 100 );
				add_action( 'admin_head', array( $this, 'hide_block_editor_discussion_panel_css' ), 100 );
			}
		}

		/**
		 * Initialize plugin hooks and apply blog restrictions.
		 */
		public function init() {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			$this->apply_blog_restrictions();
		}

		/**
		 * CSS to hide discussion settings
		 */
		public function hide_discussion_settings_css() {
			global $pagenow;
			if ( 'options-discussion.php' === $pagenow ) {
				echo '<style>body { display: none; }</style>';
				echo '<script>window.location.href = "' . esc_url( admin_url() ) . '";</script>';
			}
		}

		/**
		 * Hide Discussion panel and message in block editor sidebar with aggressive CSS
		 */
		public function hide_block_editor_discussion_panel_css() {
			if ( ! function_exists( 'get_current_screen' ) ) {
				return;
			}
			$screen = get_current_screen();
			if ( $screen && isset( $screen->post_type ) && in_array( $screen->post_type, array( 'post', 'page' ) ) && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
				echo '<style>
				.edit-post-sidebar .components-panel__body[data-title="Discussion"] { display: none !important; }
				.edit-post-sidebar [aria-label*="Discussion"] { display: none !important; }
				.edit-post-sidebar .edit-post-post-status__row:has(span:contains("Discussion")) { display: none !important; }
				.edit-post-sidebar .edit-post-post-status__row:has(span:contains("Closed")) { display: none !important; }
				.edit-post-sidebar .edit-post-post-status__row { display: none !important; }
				</style>';
			}
		}

		/**
		 * Add admin menu under Tools
		 */
		public function add_admin_menu() {
			add_management_page(
				'LC Theme Options',
				'LC Theme Options',
				'manage_options',
				'lc-theme-options',
				array( $this, 'options_page' )
			);
		}

		/**
		 * Initialize settings
		 */
		public function settings_init() {
			register_setting( 'lc_theme_options', $this->option_name, array( $this, 'sanitize_options' ) );

			add_settings_section(
				'lc_theme_options_section',
				__( 'Blog Control Options', 'lc-theme-options' ),
				array( $this, 'settings_section_callback' ),
				'lc_theme_options'
			);

			add_settings_field(
				'disable_blog',
				__( 'Disable Blog', 'lc-theme-options' ),
				array( $this, 'disable_blog_render' ),
				'lc_theme_options',
				'lc_theme_options_section'
			);

			add_settings_field(
				'disable_comments',
				__( 'Disable Comments', 'lc-theme-options' ),
				array( $this, 'disable_comments_render' ),
				'lc_theme_options',
				'lc_theme_options_section'
			);

			add_settings_field(
				'disable_gravatars',
				__( 'Disable Gravatars', 'lc-theme-options' ),
				array( $this, 'disable_gravatars_render' ),
				'lc_theme_options',
				'lc_theme_options_section'
			);

			add_settings_field(
				'disable_tags',
				__( 'Disable Tags', 'lc-theme-options' ),
				array( $this, 'disable_tags_render' ),
				'lc_theme_options',
				'lc_theme_options_section'
			);

			add_settings_field(
				'disable_emojis',
				__( 'Disable Emojis', 'lc-theme-options' ),
				array( $this, 'disable_emojis_render' ),
				'lc_theme_options',
				'lc_theme_options_section'
			);

			add_settings_field(
				'suppress_object_cache_warning',
				__( 'Suppress Object Cache Warning', 'lc-theme-options' ),
				array( $this, 'suppress_object_cache_warning_render' ),
				'lc_theme_options',
				'lc_theme_options_section'
			);
		}

		/**
		 * Sanitize and validate options before saving.
		 */
		public function sanitize_options( $input ) {
			$output = array();
			$output['disable_blog']                = ! empty( $input['disable_blog'] ) ? 1 : 0;
			$output['disable_comments']            = ! empty( $input['disable_comments'] ) ? 1 : 0;
			$output['disable_gravatars']           = ! empty( $input['disable_gravatars'] ) ? 1 : 0;
			$output['disable_tags']                = ! empty( $input['disable_tags'] ) ? 1 : 0;
			$output['disable_emojis']              = ! empty( $input['disable_emojis'] ) ? 1 : 0;
			$output['suppress_object_cache_warning'] = ! empty( $input['suppress_object_cache_warning'] ) ? 1 : 0;
			return $output;
		}

		/**
		 * Settings section callback
		 */
		public function settings_section_callback() {
			echo '<p>' . esc_html__( 'Configure blog functionality options below:', 'lc-theme-options' ) . '</p>';
		}

		/**
		 * Render disable blog checkbox
		 */
		public function disable_blog_render() {
			$options = get_option( $this->option_name );
			$checked = isset( $options['disable_blog'] ) ? $options['disable_blog'] : 0;
			?>
			<input type="checkbox" id="disable_blog" name="<?php echo esc_attr( $this->option_name ); ?>[disable_blog]" value="1" <?php checked( 1, $checked ); ?>>
			<label for="disable_blog"><?php esc_html_e( 'Disable all blog functionality (this will also disable comments and gravatars)', 'lc-theme-options' ); ?></label>
			<?php
		}

		/**
		 * Render disable comments checkbox
		 */
		public function disable_comments_render() {
			$options = get_option( $this->option_name );
			$checked = isset( $options['disable_comments'] ) ? $options['disable_comments'] : 0;
			?>
			<input type="checkbox" id="disable_comments" name="<?php echo esc_attr( $this->option_name ); ?>[disable_comments]" value="1" <?php checked( 1, $checked ); ?>>
			<label for="disable_comments"><?php esc_html_e( 'Disable comments functionality', 'lc-theme-options' ); ?></label>
			<?php
		}

		/**
		 * Render disable gravatars checkbox
		 */
		public function disable_gravatars_render() {
			$options = get_option( $this->option_name );
			$checked = isset( $options['disable_gravatars'] ) ? $options['disable_gravatars'] : 0;
			?>
			<input type="checkbox" id="disable_gravatars" name="<?php echo esc_attr( $this->option_name ); ?>[disable_gravatars]" value="1" <?php checked( 1, $checked ); ?>>
			<label for="disable_gravatars"><?php esc_html_e( 'Disable Gravatars', 'lc-theme-options' ); ?></label>
			<?php
		}

		/**
		 * Render disable tags checkbox
		 */
		public function disable_tags_render() {
			$options = get_option( $this->option_name );
			$checked = isset( $options['disable_tags'] ) ? $options['disable_tags'] : 0;
			?>
			<input type="checkbox" id="disable_tags" name="<?php echo esc_attr( $this->option_name ); ?>[disable_tags]" value="1" <?php checked( 1, $checked ); ?>>
			<label for="disable_tags"><?php esc_html_e( 'Disable Tags', 'lc-theme-options' ); ?></label>
			<?php
		}

		/**
		 * Render disable emojis checkbox
		 */
		public function disable_emojis_render() {
			$options = get_option( $this->option_name );
			$checked = isset( $options['disable_emojis'] ) ? $options['disable_emojis'] : 0;
			?>
			<input type="checkbox" id="disable_emojis" name="<?php echo esc_attr( $this->option_name ); ?>[disable_emojis]" value="1" <?php checked( 1, $checked ); ?>>
			<label for="disable_emojis"><?php esc_html_e( 'Disable WordPress Emojis (removes emoji scripts/styles from frontend and admin)', 'lc-theme-options' ); ?></label>
			<?php
		}

		/**
		 * Render suppress object cache warning checkbox
		 */
		public function suppress_object_cache_warning_render() {
			$options = get_option( $this->option_name );
			$checked = isset( $options['suppress_object_cache_warning'] ) ? $options['suppress_object_cache_warning'] : 0;
			?>
			<input type="checkbox" id="suppress_object_cache_warning" name="<?php echo esc_attr( $this->option_name ); ?>[suppress_object_cache_warning]" value="1" <?php checked( 1, $checked ); ?>>
			<label for="suppress_object_cache_warning"><?php esc_html_e( 'Suppress Site Health "persistent object cache" warning (useful for small sites where object caching is unnecessary)', 'lc-theme-options' ); ?></label>
			<?php
		}

		/**
		 * Options page HTML
		 */
		public function options_page() {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'LC Theme Options', 'lc-theme-options' ); ?></h1>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'lc_theme_options' );
					do_settings_sections( 'lc_theme_options' );
					submit_button();
					?>
				</form>
			</div>

			<script>
			jQuery(document).ready(function($) {
				$('#disable_blog').change(function() {
					if ($(this).is(':checked')) {
						$('#disable_comments').prop('checked', true);
						$('#disable_gravatars').prop('checked', true);
						$('#disable_tags').prop('checked', true);
					}
				});

				$('#disable_comments, #disable_gravatars, #disable_tags').change(function() {
					if (!$(this).is(':checked') && $('#disable_blog').is(':checked')) {
						$(this).prop('checked', true);
						alert('<?php echo esc_js( __( 'Comments, Gravatars, and Tags cannot be enabled while blog is disabled.', 'lc-theme-options' ) ); ?>');
					}
				});
			});
			</script>
			<?php
		}

		/**
		 * Apply blog restrictions based on settings
		 */
		public function apply_blog_restrictions() {
			$options = get_option( $this->option_name );

			// Add the LC dashboard widget.
			add_action( 'wp_dashboard_setup', array( $this, 'register_lc_dashboard_widget' ) );

			// Always remove unwanted dashboard widgets.
			add_action( 'wp_dashboard_setup', array( $this, 'remove_unwanted_dashboard_widgets' ) );

			// Suppress persistent object cache warning in Site Health if enabled.
			if ( isset( $options['suppress_object_cache_warning'] ) && $options['suppress_object_cache_warning'] ) {
				add_filter( 'site_status_should_suggest_persistent_object_cache', '__return_false' );
			}

			// Check if blog is disabled.
			if ( isset( $options['disable_blog'] ) && $options['disable_blog'] ) {
				$this->disable_blog_functionality();
			} else {
				if ( isset( $options['disable_comments'] ) && $options['disable_comments'] ) {
					$this->disable_comments_functionality();
				}

				if ( isset( $options['disable_gravatars'] ) && $options['disable_gravatars'] ) {
					$this->disable_gravatars_functionality();
				}

				if ( isset( $options['disable_tags'] ) && $options['disable_tags'] ) {
					$this->disable_tags_functionality();
				}
			}

			// Check if emojis should be disabled.
			if ( isset( $options['disable_emojis'] ) && $options['disable_emojis'] ) {
				add_action( 'init', array( $this, 'disable_emojis_functionality' ), 1 );
			}
		}

		/**
		 * Disable WordPress emoji scripts/styles.
		 */
		public function disable_emojis_functionality() {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
			add_filter( 'emoji_svg_url', '__return_false' );
		}

		/**
		 * Remove the emoji plugin from TinyMCE.
		 *
		 * @param array $plugins List of TinyMCE plugins.
		 * @return array Modified list of plugins.
		 */
		public function disable_emojis_tinymce( $plugins ) {
			if ( is_array( $plugins ) ) {
				return array_diff( $plugins, array( 'wpemoji' ) );
			}
			return $plugins;
		}

		/**
		 * Register the custom LC dashboard widget.
		 */
		public function register_lc_dashboard_widget() {
			wp_add_dashboard_widget(
				'lc_dashboard_widget',
				'LC Theme Options',
				array( $this, 'lc_dashboard_widget_display' )
			);
		}

		/**
		 * Display the custom LC dashboard widget.
		 */
		public function lc_dashboard_widget_display() {
			?>
			<div>
				<p><strong><?php esc_html_e( 'Thanks for using LC Theme Options!', 'lc-theme-options' ); ?></strong></p>
				<hr>
				<p><?php esc_html_e( 'This plugin helps you manage various WordPress theme options including blog, comments, gravatars, tags, and emojis.', 'lc-theme-options' ); ?></p>
				<p><?php esc_html_e( 'Configure the settings under Tools > LC Theme Options.', 'lc-theme-options' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Remove unwanted dashboard widgets
		 */
		public function remove_unwanted_dashboard_widgets() {
			// Remove "At a Glance" widget.
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );

			// Remove "WordPress Events and News" widget.
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

			// Remove "Quick Draft" widget.
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );

			// Remove "Activity" widget.
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );

			// Remove "Recent Comments" widget.
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

			// Remove "Recent Drafts" widget.
			remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );

			// Remove Yoast SEO widgets.
			remove_meta_box( 'yoast_db_widget', 'dashboard', 'normal' );
			remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' );
			remove_meta_box( 'wpseo-wincher-dashboard-overview', 'dashboard', 'normal' );
			remove_meta_box( 'yoast_seo_posts_overview', 'dashboard', 'normal' );
			remove_meta_box( 'yoast_seo_posts_overview', 'dashboard', 'side' );
			remove_meta_box( 'wpseo_dashboard_widget', 'dashboard', 'normal' );
		}

		/**
		 * Disable all blog functionality
		 */
		private function disable_blog_functionality() {
			// Hide Posts menu from admin.
			add_action( 'admin_menu', array( $this, 'remove_posts_menu' ), 999 );

			// Disable post type support.
			add_action( 'init', array( $this, 'disable_post_type' ) );

			// Redirect post-related pages.
			add_action( 'admin_init', array( $this, 'redirect_post_pages' ) );

			// Disable comments and gravatars as well.
			$this->disable_comments_functionality();
			$this->disable_gravatars_functionality();
			$this->disable_tags_functionality();

			// Remove blog-related admin bar items.
			add_action( 'admin_bar_menu', array( $this, 'remove_blog_admin_bar_items' ), 999 );
		}

		/**
		 * Remove Posts menu from admin
		 */
		public function remove_posts_menu() {
			remove_menu_page( 'edit.php' );
			remove_submenu_page( 'edit.php', 'post-new.php' );
		}

		/**
		 * Disable post type
		 */
		public function disable_post_type() {
			global $wp_post_types;
			if ( isset( $wp_post_types['post'] ) ) {
				$wp_post_types['post']->public            = false;
				$wp_post_types['post']->show_ui           = false;
				$wp_post_types['post']->show_in_menu      = false;
				$wp_post_types['post']->show_in_admin_bar = false;
				$wp_post_types['post']->show_in_nav_menus = false;
			}
		}

		/**
		 * Redirect post-related admin pages
		 */
		public function redirect_post_pages() {
			global $pagenow;

			$post_pages = array( 'edit.php', 'post-new.php', 'post.php' );

			if ( in_array( $pagenow, $post_pages, true ) ) {
				$current_post_type = null;

				if ( isset( $_REQUEST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$current_post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				} elseif ( isset( $_REQUEST['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$post_id           = intval( $_REQUEST['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$current_post_type = get_post_type( $post_id );
				}

				if ( null === $current_post_type || 'post' !== $current_post_type ) {
					return;
				}

				$allowed_actions = array( 'trash', 'delete', 'bulk-delete', 'bulk-trash' );
				$action          = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( $action && in_array( $action, $allowed_actions, true ) ) {
					return;
				}

				wp_safe_redirect( admin_url() );
				exit;
			}
		}

		/**
		 * Remove blog-related admin bar items
		 *
		 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
		 */
		public function remove_blog_admin_bar_items( $wp_admin_bar ) {
			$wp_admin_bar->remove_node( 'new-post' );
		}

		/**
		 * Disable comments functionality
		 */
		private function disable_comments_functionality() {
			// Disable comments support for all post types.
			add_action( 'init', array( $this, 'disable_comments_post_types_support' ) );

			// Close comments on existing posts.
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );

			// Hide existing comments.
			add_filter( 'comments_array', '__return_empty_array', 10, 2 );

			// Remove comment status from REST API responses.
			add_filter( 'rest_endpoints', function( $endpoints ) {
				unset( $endpoints['/wp/v2/comments'] );
				unset( $endpoints['/wp/v2/comments/(?P<id>[\\d]+)'] );
				return $endpoints;
			} );

			// Remove comments page from admin menu.
			add_action( 'admin_menu', array( $this, 'remove_comments_menu' ) );

			// Redirect comment admin pages.
			add_action( 'admin_init', array( $this, 'redirect_comment_pages' ) );

			// Remove comments from admin bar.
			add_action( 'admin_bar_menu', array( $this, 'remove_comments_admin_bar' ), 999 );

			// Remove comment-related dashboard widgets.
			add_action( 'wp_dashboard_setup', array( $this, 'remove_comment_dashboard_widgets' ) );

			// Hide discussion settings.
			add_action( 'admin_init', array( $this, 'hide_discussion_settings' ) );

			// Hide discussion menu from Settings.
			add_action( 'admin_menu', array( $this, 'remove_discussion_menu' ) );

			// Remove Discussion metabox from post/page editor.
			add_action( 'add_meta_boxes', array( $this, 'remove_discussion_metabox' ), 99 );

			// Remove Comments column from posts/pages list tables.
			add_filter( 'manage_posts_columns', array( $this, 'remove_comments_column' ) );
			add_filter( 'manage_pages_columns', array( $this, 'remove_comments_column' ) );

			// Remove comment support from REST API post responses.
			add_filter( 'rest_prepare_post', function( $response ) {
				if ( isset( $response->data['comment_status'] ) ) {
					$response->data['comment_status'] = 'closed';
				}
				return $response;
			}, 100 );
			add_filter( 'rest_prepare_page', function( $response ) {
				if ( isset( $response->data['comment_status'] ) ) {
					$response->data['comment_status'] = 'closed';
				}
				return $response;
			}, 100 );
		}

		/**
		 * Disable comments support for post types
		 */
		public function disable_comments_post_types_support() {
			$post_types = get_post_types();
			foreach ( $post_types as $post_type ) {
				if ( post_type_supports( $post_type, 'comments' ) ) {
					remove_post_type_support( $post_type, 'comments' );
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}
		}

		/**
		 * Remove comments menu
		 */
		public function remove_comments_menu() {
			remove_menu_page( 'edit-comments.php' );
		}

		/**
		 * Remove discussion menu from Settings
		 */
		public function remove_discussion_menu() {
			remove_submenu_page( 'options-general.php', 'options-discussion.php' );
		}

		/**
		 * Redirect comment pages
		 */
		public function redirect_comment_pages() {
			global $pagenow;

			if ( 'edit-comments.php' === $pagenow ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
		}

		/**
		 * Remove comments from admin bar
		 *
		 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
		 */
		public function remove_comments_admin_bar( $wp_admin_bar ) {
			$wp_admin_bar->remove_node( 'comments' );
		}

		/**
		 * Remove comment dashboard widgets
		 */
		public function remove_comment_dashboard_widgets() {
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		}

		/**
		 * Hide discussion settings
		 */
		public function hide_discussion_settings() {
			add_action( 'admin_head', array( $this, 'hide_discussion_settings_css' ) );
		}

		/**
		 * Remove Discussion metabox from post/page editor
		 */
		public function remove_discussion_metabox() {
			remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
			remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
			remove_meta_box( 'commentsdiv', 'post', 'normal' );
			remove_meta_box( 'commentsdiv', 'page', 'normal' );
		}

		/**
		 * Remove Discussion panel from block editor (Gutenberg)
		 */
		public function remove_block_editor_discussion_panel() {
			if ( function_exists( 'remove_post_editor_support' ) ) {
				remove_post_editor_support( 'post', 'comments' );
				remove_post_editor_support( 'page', 'comments' );
			}
		}

		/**
		 * Remove Comments column from posts/pages list tables
		 */
		public function remove_comments_column( $columns ) {
			if ( isset( $columns['comments'] ) ) {
				unset( $columns['comments'] );
			}
			return $columns;
		}

		/**
		 * Disable Gravatars functionality
		 */
		private function disable_gravatars_functionality() {
			add_filter( 'pre_option_show_avatars', '__return_zero' );
			add_filter( 'user_profile_picture_description', '__return_empty_string' );
			add_filter( 'get_avatar', array( $this, 'disable_gravatar' ), 10, 5 );
		}

		/**
		 * Replace avatar with empty string
		 *
		 * @param string $avatar      The avatar HTML.
		 * @param mixed  $id_or_email The user ID or email.
		 * @param int    $size        The avatar size.
		 * @param string $default_avatar     The default avatar URL.
		 * @param string $alt         The alt text.
		 * @return string Empty string to disable avatars.
		 */
		public function disable_gravatar( $avatar, $id_or_email, $size, $default_avatar, $alt ) {
			return '';
		}

		/**
		 * Disable Tags functionality
		 */
		private function disable_tags_functionality() {
			add_action( 'init', array( $this, 'unregister_tags' ), 999 );
			add_action( 'admin_menu', array( $this, 'remove_tags_menu' ) );
			add_action( 'add_meta_boxes', array( $this, 'remove_tags_metabox' ), 999 );
		}

		/**
		 * Unregister tags taxonomy for posts
		 */
		public function unregister_tags() {
			unregister_taxonomy_for_object_type( 'post_tag', 'post' );

			global $wp_taxonomies;
			if ( isset( $wp_taxonomies['post_tag'] ) ) {
				$wp_taxonomies['post_tag']->show_ui            = false;
				$wp_taxonomies['post_tag']->show_in_menu       = false;
				$wp_taxonomies['post_tag']->show_in_nav_menus  = false;
				$wp_taxonomies['post_tag']->show_tagcloud      = false;
				$wp_taxonomies['post_tag']->show_in_quick_edit = false;
				$wp_taxonomies['post_tag']->show_admin_column  = false;
			}
		}

		/**
		 * Remove tags submenu from Posts menu
		 */
		public function remove_tags_menu() {
			remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
		}

		/**
		 * Remove tags metabox from post editor
		 */
		public function remove_tags_metabox() {
			remove_meta_box( 'tagsdiv-post_tag', 'post', 'side' );
		}

	}
}

// Initialize the plugin globally so frontend filters are registered.
if ( class_exists( 'LCThemeOptions' ) && ! isset( $GLOBALS['lc_theme_options_instance'] ) ) {
	$GLOBALS['lc_theme_options_instance'] = new LCThemeOptions();
}

// Activation hook.
if ( ! function_exists( 'lc_theme_options_activation_check' ) ) {
	register_activation_hook( __FILE__, array( 'LCThemeOptions', 'activate' ) );
}

// Deactivation hook.
if ( ! function_exists( 'lc_theme_options_deactivation_check' ) ) {
	register_deactivation_hook( __FILE__, array( 'LCThemeOptions', 'deactivate' ) );
}

// Add a 'Theme Options' link to the plugin's action links on the Installed Plugins page.
add_filter(
	'plugin_action_links_lc-theme-options/lc-theme-options.php',
	function ( $links ) {
		$settings_link = '<a href="' . admin_url( 'tools.php?page=lc-theme-options' ) . '">' . __( 'Theme Options', 'lc-theme-options' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
);

/**
 * Globally disable WordPress emojis as early as possible if option is set.
 */
add_action(
	'plugins_loaded',
	function () {
		$options = get_option( 'lc_theme_options' );
		if ( isset( $options['disable_emojis'] ) && $options['disable_emojis'] ) {
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'emoji_svg_url', '__return_false' );
			add_filter(
				'tiny_mce_plugins',
				function ( $plugins ) {
					if ( is_array( $plugins ) ) {
						return array_diff( $plugins, array( 'wpemoji' ) );
					}
					return $plugins;
				}
			);
		}
	},
	1
);

/**
 * Force all ACF blocks to always display in edit mode in the block editor.
 * This JS subscriber watches the block store and resets any ACF block that drifts to preview/auto.
 */
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_add_inline_script(
			'wp-block-editor',
			"( function () {\n\tvar pending = {};\n\tvar watcherStarted = false;\n\n\tfunction getEditorSelect() {\n\t\treturn window.wp && wp.data && wp.data.select ? wp.data.select( 'core/block-editor' ) : null;\n\t}\n\n\tfunction getEditorDispatch() {\n\t\treturn window.wp && wp.data && wp.data.dispatch ? wp.data.dispatch( 'core/block-editor' ) : null;\n\t}\n\n\tfunction queueEditMode( clientId ) {\n\t\tif ( pending[ clientId ] ) {\n\t\t\treturn;\n\t\t}\n\n\t\tpending[ clientId ] = true;\n\n\t\twindow.requestAnimationFrame( function () {\n\t\t\tvar select = getEditorSelect();\n\t\t\tvar dispatch = getEditorDispatch();\n\t\t\tvar block = select && select.getBlock ? select.getBlock( clientId ) : null;\n\n\t\t\tpending[ clientId ] = false;\n\n\t\t\tif ( ! block || ! block.name || block.name.indexOf( 'acf/' ) !== 0 ) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif ( block.attributes && block.attributes.mode === 'edit' ) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif ( dispatch && dispatch.updateBlockAttributes ) {\n\t\t\t\tdispatch.updateBlockAttributes( clientId, { mode: 'edit' } );\n\t\t\t\twindow.setTimeout( function () {\n\t\t\t\t\tvar refreshedSelect = getEditorSelect();\n\t\t\t\t\tvar refreshedBlock = refreshedSelect && refreshedSelect.getBlock ? refreshedSelect.getBlock( clientId ) : null;\n\n\t\t\t\t\tif ( refreshedBlock && refreshedBlock.attributes && refreshedBlock.attributes.mode !== 'edit' ) {\n\t\t\t\t\t\tqueueEditMode( clientId );\n\t\t\t\t\t}\n\t\t\t\t}, 50 );\n\t\t\t}\n\t\t} );\n\t}\n\n\tfunction forceAllAcfBlocksToEdit() {\n\t\tvar select = getEditorSelect();\n\t\tvar clientIds = select && select.getClientIdsWithDescendants ? select.getClientIdsWithDescendants() : [];\n\n\t\tclientIds.forEach( function ( clientId ) {\n\t\t\tvar blockName = select.getBlockName ? select.getBlockName( clientId ) : '';\n\n\t\t\tif ( blockName && blockName.indexOf( 'acf/' ) === 0 ) {\n\t\t\t\tqueueEditMode( clientId );\n\t\t\t}\n\t\t} );\n\t}\n\n\tfunction startWatcher() {\n\t\tif ( watcherStarted ) {\n\t\t\treturn;\n\t\t}\n\n\t\twatcherStarted = true;\n\n\t\tforceAllAcfBlocksToEdit();\n\n\t\twp.data.subscribe( function () {\n\t\t\twindow.requestAnimationFrame( function () {\n\t\t\t\tforceAllAcfBlocksToEdit();\n\t\t\t} );\n\t\t} );\n\t}\n\n\tif ( document.readyState === 'complete' || document.readyState === 'interactive' ) {\n\t\tstartWatcher();\n\t} else {\n\t\tdocument.addEventListener( 'DOMContentLoaded', startWatcher );\n\t}\n} )()"
		);
	}
);
