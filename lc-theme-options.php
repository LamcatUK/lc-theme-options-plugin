<?php
/**
 * Plugin Name: LC Theme Options
 * Plugin URI: https://github.com/LamcatUK/lc-theme-options
 * Description: A WordPress plugin to manage theme options including disabling blog, comments, and gravatars.
 * Version: 1.0.0
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
	define( 'LC_THEME_OPTIONS_VERSION', '1.0.0' );
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
	 * Handles the LC Theme Options plugin functionality including disabling blog, comments, and gravatars.
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
			// Set default options.
			$default_options = array(
				'disable_blog'      => 0,
				'disable_comments'  => 1,
				'disable_gravatars' => 1,
				'disable_tags'      => 0,
			);
			add_option( 'lc_theme_options', $default_options );
		}

		/**
		 * Plugin deactivation callback.
		 *
		 * @return void
		 */
		public static function deactivate() {
			// Optional: Clean up options on deactivation.
			// delete_option('lc_theme_options');
		}

		/**
		 * LCThemeOptions constructor.
		 *
		 * Initializes the plugin by hooking into WordPress actions.
		 */
		/**
		 * Constructor: Only load admin code in admin area.
		 */
		public function __construct() {
			if ( is_admin() ) {
				add_action( 'init', array( $this, 'init' ) );
				add_action( 'init', array( $this, 'remove_block_editor_discussion_panel' ), 100 );
				add_action( 'admin_head', array( $this, 'hide_block_editor_discussion_panel_css' ), 100 );
			}
		}

		/**
		 * Initialize plugin hooks and apply blog restrictions.
		 */
		public function init() {
			// Add admin menu.
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

			// Initialize settings.
			add_action( 'admin_init', array( $this, 'settings_init' ) );

			// Apply functionality based on settings.
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
			// Only output CSS on block editor screens for post/page types
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
		}
		/**
		 * Sanitize and validate options before saving.
		 */
		public function sanitize_options( $input ) {
			$output = array();
			$output['disable_blog']      = !empty( $input['disable_blog'] ) ? 1 : 0;
			$output['disable_comments']  = !empty( $input['disable_comments'] ) ? 1 : 0;
			$output['disable_gravatars'] = !empty( $input['disable_gravatars'] ) ? 1 : 0;
			$output['disable_tags']      = !empty( $input['disable_tags'] ) ? 1 : 0;
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
		 * Options page HTML
		 */
		public function options_page() {
			?>
			<div class="wrap">
				<h1>LC Theme Options</h1>
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
				// Handle disable blog checkbox logic
				$('#disable_blog').change(function() {
					if ($(this).is(':checked')) {
						$('#disable_comments').prop('checked', true);
						$('#disable_gravatars').prop('checked', true);
						$('#disable_tags').prop('checked', true);
					}
				});
				
				// Prevent unchecking comments/gravatars/tags if blog is disabled
				$('#disable_comments, #disable_gravatars, #disable_tags').change(function() {
					if (!$(this).is(':checked') && $('#disable_blog').is(':checked')) {
						$(this).prop('checked', true);
						alert('Comments, Gravatars, and Tags cannot be enabled while blog is disabled.');
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

			// Always remove unwanted dashboard widgets.
			add_action( 'wp_dashboard_setup', array( $this, 'remove_unwanted_dashboard_widgets' ) );

			// Check if blog is disabled.
			if ( isset( $options['disable_blog'] ) && $options['disable_blog'] ) {
				$this->disable_blog_functionality();
			} else {
				// Check individual options.
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

			// Remove Yoast SEO widgets.
			remove_meta_box( 'yoast_db_widget', 'dashboard', 'normal' );
			remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' );
			remove_meta_box( 'wpseo-wincher-dashboard-overview', 'dashboard', 'normal' );

			// Remove additional Yoast widgets that might exist.
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

			// Remove post-related dashboard widgets.
			add_action( 'wp_dashboard_setup', array( $this, 'remove_post_dashboard_widgets' ) );

			// Disable comments and gravatars as well.
			$this->disable_comments_functionality();
			$this->disable_gravatars_functionality();
			$this->disable_tags_functionality();

			// Remove blog-related admin bar items.
			add_action( 'admin_bar_menu', array( $this, 'remove_blog_admin_bar_items' ), 999 );

			// Hide blog-related quick draft widget.
			add_action( 'wp_dashboard_setup', array( $this, 'remove_quick_draft_widget' ) );
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

			// Only redirect if editing a standard post, not custom post types (e.g. acf-field-group)
			if ( in_array( $pagenow, $post_pages, true ) ) {
				$post_type = null;
				if ( isset( $_GET['post_type'] ) ) {
					$post_type = $_GET['post_type'];
				} elseif ( isset( $_GET['post'] ) ) {
					$post_id = intval( $_GET['post'] );
					if ( $post_id ) {
						$post_obj = get_post( $post_id );
						if ( $post_obj ) {
							$post_type = $post_obj->post_type;
						}
					}
				}
				if ( $post_type === 'post' || ( ! isset( $post_type ) && $pagenow !== 'post.php' ) ) {
					wp_safe_redirect( admin_url() );
					exit;
				}
			}
		}

		/**
		 * Remove post-related dashboard widgets
		 */
		public function remove_post_dashboard_widgets() {
			remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
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
		 * Remove quick draft widget
		 */
		public function remove_quick_draft_widget() {
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		}

		/**
		 * Disable comments functionality
		 */
		private function disable_comments_functionality() {
			// Disable comments support for all post types.
			add_action( 'init', array( $this, 'disable_comments_post_types_support' ) );

			// Force comments and pings closed everywhere.
			add_filter( 'comments_open', '__return_false', 100, 2 );
			add_filter( 'pings_open', '__return_false', 100, 2 );
			add_filter( 'comments_array', '__return_empty_array', 100, 2 );

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
			// Disable gravatars.
			add_filter( 'pre_option_show_avatars', '__return_zero' );

			// Remove avatar from user profile.
			add_filter( 'user_profile_picture_description', '__return_empty_string' );

			// Replace avatar with blank image or remove entirely.
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
		public function disable_tags_functionality() {
			// Remove tags support from posts.
			add_action( 'init', array( $this, 'unregister_tags' ) );
			add_action( 'init', array( $this, 'remove_post_tag_support' ), 999 );
			add_action( 'init', array( $this, 'completely_remove_tags' ), 999 );
			add_action( 'admin_menu', array( $this, 'remove_tags_menu' ) );
			add_action( 'add_meta_boxes', array( $this, 'remove_tags_metabox' ), 999 );
			add_action( 'admin_head', array( $this, 'hide_tags_with_css' ) );
		}

		/**
		 * Unregister tags taxonomy for posts
		 */
		public function unregister_tags() {
			unregister_taxonomy_for_object_type( 'post_tag', 'post' );
		}

		/**
		 * Completely remove tags taxonomy
		 */
		public function completely_remove_tags() {
			global $wp_taxonomies;
			if ( isset( $wp_taxonomies['post_tag'] ) ) {
				unset( $wp_taxonomies['post_tag'] );
			}
			unregister_taxonomy( 'post_tag' );
		}

		/**
		 * Remove post tag support entirely
		 */
		public function remove_post_tag_support() {
			remove_post_type_support( 'post', 'post-tags' );
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
			remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
			remove_meta_box( 'tagsdiv-post_tag', 'post', 'advanced' );
			// Also try alternative names.
			remove_meta_box( 'post_tag', 'post', 'side' );
			remove_meta_box( 'post_tagdiv', 'post', 'side' );
			remove_meta_box( 'post_tag', 'post', 'normal' );
			remove_meta_box( 'post_tagdiv', 'post', 'normal' );
		}

		/**
		 * Hide tags metabox with CSS as last resort
		 */
		public function hide_tags_with_css() {
			global $pagenow;

			if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
				echo '<style>
					#tagsdiv-post_tag,
					#post_tag,
					#post_tagdiv,
					.tagsdiv,
					.postbox#tagsdiv-post_tag,
					div[id*="tag"],
					.meta-box-sortables #tagsdiv-post_tag {
						display: none !important;
					}
				</style>';
			}
		}
	}
} // End if class_exists check.

// Initialize the plugin only if the class exists and hasn't been initialized yet.
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
?>
