<?php
/**
 * Class Analytics
 *
 * @package   Google\Web_Stories
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://github.com/google/web-stories-wp
 */

/**
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Web_Stories;

/**
 * Class Analytics
 */
class Analytics {
	/**
	 * Initializes all hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'googlesitekit_amp_gtag_opt', [ $this, 'filter_site_kit_gtag_opt' ] );
		add_action( 'web_stories_print_analytics', [ $this, 'print_analytics_tag' ] );
	}

	/**
	 * Determines whether the built-in Analytics module in Site Kit is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether Site Kit's analytics module is active.
	 */
	protected function is_site_kit_analytics_module_active() {
		$modules = $this->get_site_kit_active_modules_option();

		return in_array( 'analytics', $modules, true );
	}

	/**
	 * Gets the option containing the active Site Kit modules.
	 *
	 * Checks two options as it was renamed at some point in Site Kit.
	 *
	 * Bails early if the Site Kit plugin itself is not active.
	 *
	 * @see \Google\Site_Kit\Core\Modules\Modules::get_active_modules_option
	 *
	 * @return array List of active module slugs.
	 */
	private function get_site_kit_active_modules_option() {
		if ( ! defined( 'GOOGLESITEKIT_VERSION' ) ) {
			return [];
		}

		$option = get_option( 'googlesitekit_active_modules' );

		if ( is_array( $option ) ) {
			return $option;
		}

		$legacy_option = get_option( 'googlesitekit-active-modules' );

		if ( is_array( $legacy_option ) ) {
			return $legacy_option;
		}

		return [];
	}

	/**
	 * Returns the  Google Analytics tracking ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Tracking ID.
	 */
	public function get_tracking_id() {
		return (string) get_option( Settings::SETTING_NAME_TRACKING_ID );
	}

	/**
	 * Returns the default analytics configuration.
	 *
	 * Note: variables in single quotes will be substituted by <amp-analytics>.
	 *
	 * @see https://github.com/ampproject/amphtml/blob/master/spec/amp-var-substitutions.md
	 *
	 * @param string $tracking_id Tracking ID.
	 * @return array <amp-analytics> configuration.
	 */
	public function get_default_configuration( $tracking_id ) {
		$config = [
			'vars'     => [
				'gtag_id' => $tracking_id,
				'config'  => [
					$tracking_id => [ 'groups' => 'default' ],
				],
			],
			'triggers' => [
				// Fired when a story page becomes visible.
				'storyProgress'       => [
					'on'      => 'story-page-visible',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_progress',
						'event_category' => '${title}',
						'event_label'    => '${storyPageIndex}',
						'event_value'    => '${storyProgress}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when the last page in the story is shown to the user.
				// This can be used to measure completion rate.
				'storyEnd'            => [
					'on'      => 'story-last-page-visible',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_complete',
						'event_category' => '${title}',
						'event_label'    => '${storyPageCount}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when clicking an element that opens a tooltip (<a> or <amp-twitter>).
				'trackFocusState'     => [
					'on'      => 'story-focus',
					'tagName' => 'a',
					'request' => 'click ',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_focus',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when clicking on a tooltip.
				'trackClickThrough'   => [
					'on'      => 'story-click-through',
					'tagName' => 'a',
					'request' => 'click ',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_click_through',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when opening a drawer or dialog inside a story (e.g. page attachment).
				'storyOpen'           => [
					'on'      => 'story-open',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_open',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when closing a drawer or dialog inside a story (e.g. page attachment).
				'storyClose'          => [
					'on'      => 'story-close',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_close',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when the user initiates an interaction to mute the audio for the current story.
				'audioMuted'          => [
					'on'      => 'story-audio-muted',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_audio_muted',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when the user initiates an interaction to unmute the audio for the current story.
				'audioUnmuted'        => [
					'on'      => 'story-audio-unmuted',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_audio_unmuted',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when a page attachment is opened by the user.
				'pageAttachmentEnter' => [
					'on'      => 'story-page-attachment-enter',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_page_attachment_enter',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
				// Fired when a page attachment is dismissed by the user.
				'pageAttachmentExit'  => [
					'on'      => 'story-page-attachment-exit',
					'request' => 'event',
					'vars'    => [
						'event_name'     => 'custom',
						'event_action'   => 'story_page_attachment_exit',
						'event_category' => '${title}',
						'send_to'        => $tracking_id,
					],
				],
			],
		];

		return (array) apply_filters( 'web_stories_analytics_configuration', $config );
	}

	/**
	 * Prints the <amp-analytics> tag for single stories.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print_analytics_tag() {
		if ( $this->is_site_kit_analytics_module_active() ) {
			return;
		}

		$tracking_id = $this->get_tracking_id();

		if ( ! $tracking_id ) {
			return;
		}
		?>
		<amp-analytics type="gtag" data-credentials="include">
			<script type="application/json">
				<?php echo wp_json_encode( $this->get_default_configuration( $tracking_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</script>
		</amp-analytics>
		<?php
	}

	/**
	 * Filters Site Kit's Google Analytics configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $gtag_opt Array of gtag configuration options.
	 *
	 * @return array Modified configuration options.
	 */
	public function filter_site_kit_gtag_opt( $gtag_opt ) {
		if ( ! is_singular( Story_Post_Type::POST_TYPE_SLUG ) ) {
			return $gtag_opt;
		}

		$default_config             = $this->get_default_configuration( $gtag_opt['vars']['gtag_id'] );
		$default_config['triggers'] = isset( $default_config['triggers'] ) ? $default_config['triggers'] : [];

		$gtag_opt['triggers'] = isset( $gtag_opt['triggers'] ) ? $gtag_opt['triggers'] : [];
		$gtag_opt['triggers'] = array_merge(
			$default_config['triggers'],
			$gtag_opt['triggers']
		);

		return $gtag_opt;
	}
}
