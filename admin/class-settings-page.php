<?php

/**
 * Settings Page
 *
 * @package Metanotify\Admin
 */

/**
 * Metanotify_Settings_Page
 *
 * Displaying the Settings Page
 */
final class Metanotify_Settings_Page
{
	/**
	 * @var string
	 */
	const SETTINGS_GROUP = 'metanotifySettingsGroup';

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * Singleton
	 */
	public static function init()
	{
		static $self = null;

		if (null === $self) {
			$self = new self;
			add_action('admin_menu', array($self, 'add_admin_menu'),10);
			add_action('admin_init', array($self, 'register_setting_group'), 10, 0);
			add_action('admin_enqueue_scripts', array($self, 'enqueueScripts'));

		}
	}

	/**
	 * Constructor
	 */
	private function __construct()
	{

		$this->settings = array_merge(
			array(
				'categories' => [],
				'bg_color' => '#FFFF',
				'connect_button_text_color' => 'rgba(209, 228, 221, 1)',
				'connect_button_bg_color' => '#841212',
				'connect_button_bg_hover_color' => '#C17985',
				'connect_button_text_hover_color' => 'rgba(40, 48, 61, 1)',
				'thanks_button_text_color' => '#000000',
				'thanks_button_bg_color' => '#A7A7A7',
				'thanks_button_bg_hover_color' => 'rgba(209, 228, 221, 1)',
				'thanks_button_text_hover_color' => 'rgba(40, 48, 61, 1)',
				'message_color' => '#000000',
				'button_text_size' => 18,
				'connect_button_border_size' => 3,
				'thanks_button_border_size' => 2,
				'message_text_size' => 24,
				'button_padding_x' => 15,
				'button_padding_y' => 30,
				'name' => 'Metanotify',
				'button_text' => __('Connect Wallet for meta-notify', 'meta-notify'),
				'message' => __('Sign up and get notified when we release new content by connecting your wallet.', 'meta-notify'),
				'balance_message' => __('Sorry, insufficient balance!', 'meta-notify'),
				'cookie_duration' => 48,
				'excerpt_length' => 150,
				'consent_text' => __('I accept the Privacy Policy.', 'meta-notify'),
				'checkbox_consent_state' => '',
				'disable_auto_insert' => false,
				'privacy_policy_url' => 'https://www.blackworks.io/metaplugin-terms',
				'receiver_wallet' => '',
				'solana_receiver_wallet' => '',
				'charge_amount' => 0,
				'solana_charge_amount' => 0,
				'minimum_balance' => 0.0000,
			),
			(array) get_option('metanotifySettings')
		);
	}

	// /**
	//  * Add page
	//  *
	//  * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	//  */
	// public function add_menu_page()
	// {
	// 	$this->hook_name = add_submenu_page('metanotify-tos', __('Settings', 'meta-notify'), __('Settings', 'meta-notify'), 'manage_options', 'metanotify-settings', array($this, 'render'));
	// }

	/**
	 * Add menu page
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_admin_menu($context)
	{
		$this->hook_name = add_menu_page(
			'Meta Notify',
			'Meta Notify',
			'activate_plugins',
			'metanotify-settings',
			[$this, 'render'],
			'dashicons-welcome-view-site'
		);

		add_submenu_page(
			'metanotify-settings', __('Settings', 'meta-notify'), __('Settings', 'meta-notify'), 'manage_options', 'metanotify-settings', array($this, 'render')
		);
	}

	/**
	 * Register setting group
	 *
	 * @internal Used as a callback
	 */
	public function register_setting_group()
	{
		register_setting(self::SETTINGS_GROUP, 'metanotifySettings', array($this, 'sanitize'));
	}

	/**
	 * Sanitize form data
	 *
	 * @internal Used as a callback
	 * @var array $data Submiting data
	 */
	public function sanitize(array $data)
	{
		if (!empty($data['icon'])) {
			$data['icon'] = sanitize_text_field($data['icon']);
		}

		if (!empty($data['name'])) {
			$data['name'] = sanitize_text_field($data['name']);
		}

		if (!empty($data['message'])) {
			$data['message'] = sanitize_text_field($data['message']);
		}

		if (!empty($data['button_text'])) {
			$data['button_text'] = sanitize_text_field($data['button_text']);
		}

		if (!empty($data['checkbox_consent_state'])) {
			$data['checkbox_consent_state'] = absint($data['checkbox_consent_state']);
		}

		if (!empty($data['receiver_wallet'])) {
			$data['receiver_wallet'] = sanitize_text_field($data['receiver_wallet']);
		}

		if (!empty($data['charge_amount'])) {
			$data['charge_amount'] = floatval($data['charge_amount']);
		}

		if (!empty($data['minimum_balance'])) {
			$data['minimum_balance'] = floatval($data['minimum_balance']);
		}

		return $data;
	}

	/**
	 * Render the settings page
	 *
	 * @internal  Callback.
	 */
	public function render($page_data)
	{
		?>

		<div class="wrap">
			<h1>
			<?php echo esc_html(__('Metanotify Settings', 'meta-notify')); ?>
			</h1>


			<form method="post" action="options.php" novalidate="novalidate">
				<?php settings_fields(self::SETTINGS_GROUP); ?>
				<div class="settings-tab">
					<table class="form-table">
						<tr>
							<th scope="row">
							<?php echo esc_html(__('Infura Project API-Key', 'meta-notify')); ?>
							</th>
							<td>
								<input style="width:300px" type="text" name="<?= $this->get_name('infura_project_id') ?>"
									value="<?= $this->get_value('infura_project_id') ?>">
								<p class="description">
									<?php echo esc_html(__('Get your infura project API-KEY by signing up  <a href="https://infura.io/register" target="_blank"> here</a>. Choose <b>Web3 API</b> as <b>network</b> and give a nice <b>name</b> of your choice. Copy the API-KEY from the next window. ', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Message Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-message-color" data-owner="metaNotifyMessageColor">
								<input id="metaNotifyMessageColor" name="<?= $this->get_name('message_color'); ?>" type="hidden"
									value="<?= $this->get_value('message_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Text color of the callout message.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Message Font Size', 'meta-notify')); ?>
							</th>
							<td>
								<input name="<?= $this->get_name('message_text_size'); ?>" type="number"
									value="<?= $this->get_value('message_text_size'); ?>" placeholder=""> px
								<p class="description">
									<?php echo esc_html(__('Text size of the heading message.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Background Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-bg-color" data-owner="metaNotifyBgColor">
								<input id="metaNotifyBgColor" name="<?= $this->get_name('bg_color'); ?>" type="hidden"
									value="<?= $this->get_value('bg_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Background color of the whole callout.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Text Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-color" data-owner="metaNotifyButtonColor">
								<input id="metaNotifyButtonColor" name="<?= $this->get_name('connect_button_text_color'); ?>"
									type="hidden" value="<?= $this->get_value('connect_button_text_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Text color of the Connect button.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('No Thanks Button Text Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-color-2" data-owner="metaNotifyThanksColor">
								<input id="metaNotifyThanksColor" name="<?= $this->get_name('thanks_button_text_color'); ?>"
									type="hidden" value="<?= $this->get_value('thanks_button_text_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Text color of the No Thanks button.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Hover Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-hover-color"
									data-owner="metaNotifyButtonHoverColor">
								<input id="metaNotifyButtonHoverColor"
									name="<?= $this->get_name('connect_button_text_hover_color'); ?>" type="hidden"
									value="<?= $this->get_value('connect_button_text_hover_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Text color of the connect button on hover.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>

						<tr>
							<th scope="row">
								<?php echo esc_html(__('No Thanks Button Hover Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-hover-color-2"
									data-owner="metaNotifyButtonHoverColor">
								<input id="metaNotifyButtonHoverColor"
									name="<?= $this->get_name('thanks_button_text_hover_color'); ?>" type="hidden"
									value="<?= $this->get_value('thanks_button_text_hover_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Text color of the No Thanks button on hover.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>


						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Background Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-bg-color"
									data-owner="metaNotifyButtonBgColor">
								<input id="metaNotifyButtonBgColor" name="<?= $this->get_name('connect_button_bg_color'); ?>"
									type="hidden" value="<?= $this->get_value('connect_button_bg_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Background color of the Connect button.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html(__('Disable Auto Insert', 'meta-notify')); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?= $this->get_name('disable_auto_insert') ?>" value="1" <?php checked($this->get_value('disable_auto_insert'), 1) ?>>
									<span class="description"><?php echo esc_html(__('By default, Metanotofy will show popup in the content of every post automatically. If you want to disable that, check this box.', 'meta-locker')); ?>
</span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('No Thanks Button Background Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-bg-color-2"
									data-owner="metaNotifyButtonBgColor">
								<input id="metaNotifyButtonBgColor" name="<?= $this->get_name('thanks_button_bg_color'); ?>"
									type="hidden" value="<?= $this->get_value('thanks_button_bg_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Background color of the No Thanks button.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Background Hover Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-bg-hover-color"
									data-owner="metaNotifyButtonBgHoverColor">
								<input id="metaNotifyButtonBgHoverColor"
									name="<?= $this->get_name('connect_button_bg_hover_color'); ?>" type="hidden"
									value="<?= $this->get_value('connect_button_bg_hover_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Background color of the Connect button on hover.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('No Thanks Button Background Hover Color', 'meta-notify')); ?>
							</th>
							<td>
								<input type="text" class="meta-notify-pick-button-bg-hover-color-2"
									data-owner="metaNotifyButtonBgHoverColor">
								<input id="metaNotifyButtonBgHoverColor"
									name="<?= $this->get_name('thanks_button_bg_hover_color'); ?>" type="hidden"
									value="<?= $this->get_value('thanks_button_bg_hover_color'); ?>">
								<p class="description">
									<?php echo esc_html(__('Background color of the No Thanks button on hover.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>


						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Text', 'meta-notify')); ?>
							</th>
							<td>
								<input name="<?= $this->get_name('button_text'); ?>" type="text"
									value="<?= $this->get_value('button_text'); ?>" placeholder="">
								<p class="description">
									<?php echo esc_html(__('Text of the Connect button.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Font Size', 'meta-notify')); ?>
							</th>
							<td>
								<input name="<?= $this->get_name('button_text_size'); ?>" type="number"
									value="<?= $this->get_value('button_text_size'); ?>" placeholder=""> px
								<p class="description">
									<?php echo esc_html(__('Font Size for Connect button', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Border Size', 'meta-notify')); ?>
							</th>
							<td>
								<input name="<?= $this->get_name('connect_button_border_size'); ?>" type="number"
									value="<?= $this->get_value('connect_button_border_size'); ?>" placeholder=""> px
								<p class="description">
									<?php echo esc_html(__('Border Size for Connect button', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('No Thanks Button Border Size', 'meta-notify')); ?>
							</th>
							<td>
								<input name="<?= $this->get_name('thanks_button_border_size'); ?>" type="number"
									value="<?= $this->get_value('thanks_button_border_size'); ?>" placeholder=""> px
								<p class="description">
									<?php echo esc_html(__('Border Size for No Thanks button', 'meta-notify')); ?>
								</p>
							</td>
						</tr>


						<tr>
							<th scope="row">
								<?php echo esc_html(__('Connect Button Paddings', 'meta-notify')); ?>
							</th>
							<td>
								<input name="<?= $this->get_name('button_padding_x'); ?>" type="number"
									value="<?= $this->get_value('button_padding_x'); ?>">
								<input name="<?= $this->get_name('button_padding_y'); ?>" type="number"
									value="<?= $this->get_value('button_padding_y'); ?>"> px
								<p class="description">
									<?php echo esc_html(__('Horizontal padding.', 'meta-notify')); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo esc_html(__('Vertical padding.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>

						<tr>
							<th scope="row">
								<?php echo esc_html(__('Default Callout Message', 'meta-notify')); ?>
							</th>
							<td>
								<textarea name="<?= $this->get_name('message'); ?>" cols="60"
									rows="4"><?= $this->get_value('message'); ?></textarea>
								<p class="description">
									<?php echo esc_html(__('The message asking users to connect their wallet.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Checkbox Consent Text', 'meta-notify')); ?>
							</th>
							<td>
								<textarea name="<?= $this->get_name('consent_text'); ?>" cols="60"
									rows="4"><?= $this->get_value('consent_text'); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Checkbox Consent Checked?', 'meta-notify')); ?>
							</th>
							<td>
								<label>
									<input type="checkbox" name="<?= $this->get_name('checkbox_consent_state') ?>" value="1"
										<?php checked($this->get_value('checkbox_consent_state'), 1) ?>>
									<span class="description">
										<?= __('Yes, checked by default.', 'meta-notify') ?>
									</span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Shortcode', 'metanotify')); ?>
							</th>
							<td>
								<input style="color:#2c3338" type="text" value="[metanotify]" disabled>
								<p class="description">
									<?php echo esc_html(__('Read-only. The shortcode to display the textbox for Push Notification.', 'meta-auth')); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html(__('Privacy Policy Page URL', 'meta-notify')); ?>
							</th>
							<td>
								<input style="width:300px" type="text" name="<?= $this->get_name('privacy_policy_url') ?>"
									value="<?= $this->get_value('privacy_policy_url') ?>">
								<p class="description">
									<?php echo esc_html(__('The URL of the Privacy Policy page.', 'meta-notify')); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>
				<?php submit_button(); ?>
			</form>
			<?php
	}

	/**
	 * Enqueue assets
	 *
	 * @internal  Used as a callback.
	 */
	public function enqueueScripts($hook_name)
	{
		if ($hook_name !== $this->hook_name) {
			return;
		}

		wp_enqueue_style('pickr-classic', META_NOTIFY_URI . 'assets/css/vendor/pickr-classic.min.css', array(), META_NOTIFY_VER);

		wp_enqueue_script('metanotify_settings-page', META_NOTIFY_URI . 'assets/js/settings-page.min.js', array(), META_NOTIFY_VER, true);
	}

	/**
	 * Get name
	 *
	 * @param  string $field  Key name.
	 *
	 * @return  string
	 */
	private function get_name($key)
	{
		return 'metanotifySettings[' . $key . ']';
	}

	/**
	 * Get value
	 *
	 * @param  string $key  Key name.
	 *
	 * @return  mixed
	 */
	private function get_value($key)
	{
		return isset($this->settings[$key]) ? sanitize_text_field($this->settings[$key]) : '';
	}
}

// Initialize the Singleton.
Metanotify_Settings_Page::init();