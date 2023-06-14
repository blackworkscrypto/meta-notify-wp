<?php

/**
 * MetanotifyLicenseManager
 *
 * Show the Terms & Conditions consent after activation.
 */
final class MetanotifyLicenseManager
{
	/**
	 * Singleton
	 */
	private function __construct()
	{
		// Nope
	}

	/**
	 * Singleton
	 */
	public static function init()
	{
		static $self = null;

		if (null === $self) {
			$self = new self;
		}

		add_action('admin_menu', array($self, 'add_admin_menu'),30);
		add_action('admin_init', array($self, 'setup'), PHP_INT_MAX, 0);
		add_action('admin_enqueue_scripts', array($self, 'enqueue_assets'));
	}

	/**
	 * Add menu page to the admin dashboard
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_admin_menu($context)
	{
		$activated = get_option('metanotifyActivated');

		if (!$activated) {
			$status = "";

			$status = MetanotifyRestApi::getActivationStatus('meta-notify');

			if ('registered' === $status) {
				update_option('metanotifyActivated', 1);
			}
		}

		if (!get_option('metanotifyActivated')) {
			$this->hook_name = add_submenu_page('metanotify-settings', __('Plugin Activation', 'meta-notify'), __('Plugin Activation', 'meta-notify'), 'manage_options', 'metanotify-activation', array($this, 'render'));
		}
	}

	/**
	 * Route to this page on activation
	 *
	 * @internal Used as a callback.
	 */
	public function setup()
	{
		$run_setup = get_transient('metanotify_init_activation') && !get_option('metanotifyActivated');

		if ($run_setup) {

			if (delete_transient('metanotify_init_activation')) {
				wp_safe_redirect(admin_url('admin.php?page=metanotify-activation'));
				exit;
			}
		}
	}

	/**
	 * Render the menu page
	 *
	 * @internal Callback.
	 */
	public function render($page_data)
	{
		$siteurl = get_site_url();
		$admin_email = get_option('admin_email');


		?>
		<div class="wrap metanotify-activation-page">
			<div class="card-top">
				<img class="img" src="<?php echo META_NOTIFY_URI . 'assets/images/logo.png'; ?>" alt="Logo">
				<p id="messager" class="description">
				<?php echo esc_html(__('One more minute, please accept our Terms & Conditions!', 'meta-notify')); ?>
				</p>

				<form method="POST" action="">

					<label>
						<input id="registration_email" type="hidden" name="registration_email" value="<?= $admin_email ?>">
					</label>
					<label>
						<input id="accept_tos" type="checkbox" name="accept_tos" value="1">
						<span>
							<?= sprintf(__('I agree to the %sTerms & Conditions%s.', 'meta-notify'), '<a href="' . admin_url('admin.php?page=metanotify-tos') . '" target="_blank">', '</a>') ?>
						</span>
					</label>

					<div class="card-bottom">
						<button id="meta-plugin-activate-btn" class="button button-primary" type="submit"
							data-plugin="meta-notify">
							<?php echo esc_html(__('Activate', 'meta-notify')); ?>

						</button>
						<a class="to-dashboard" href="<?= admin_url() ?>">&larr;
						<?php echo esc_html( __( 'Back to dashboard', 'meta-notify' ) ); ?>
						</a>
					</div>
				</form>
				<p class="permalink">
				<?php echo esc_html(__('Make sure to use <b>Settings >> Permalinks >> Post name (/%postname%/)</b> before activating this plugin.', 'meta-notify')); ?>
				</p>
			</div>
		</div>
		<?php


	}

	/**
	 * Enqueue assets
	 *
	 * @internal Used as a callback.
	 */
	public function enqueue_assets($hook_name)
	{
		if (!isset($this->hook_name) || $hook_name !== $this->hook_name) {
			return;
		}

		wp_add_inline_style('dashicons', '
		@media only screen and (max-width: 782px) {
			#messager.err{color:#f22424;padding:19.5px 10px;}#messager.ok{color:#11bd40;padding:19.5px 10px;}
			.img{width:100px;height:100px;padding:10px 10px;}
			.card-top{box-shadow: 2.5px 2.5px 5px 2.5px #C0C7CA;margin-top:32px;width:300px;height:479px;}
			.card-bottom{margin-top: 15px;display:flex;flex-direction:column;align-items:center;background-color:#C0C7CA;width:100%;}
			.permalink{color:red;font-size:15px;padding:10px 10px;}
			.notice,.updated{display:none !important}
			.wp-admin{background-color:#fff !important}
			.metanotify-activation-page{margin:0 !important}
			.metanotify-activation-page h1{font-weight:600;font-size:28px}
			.metanotify-activation-page form{display:flex;flex-direction:column;align-items:center;}
			.metanotify-activation-page form label{display:block;margin-bottom:2px}
			.metanotify-activation-page form input[type="email"]{width:100%;padding:10px 10px}
			.metanotify-activation-page form .button{padding:10px 10px;text-transform:uppercase;margin-bottom:12px;margin-top:12px;}
			.metanotify-activation-page form .to-dashboard{text-decoration:none}
			.metanotify-activation-page h1,.metanotify-activation-page p{padding:10px 10px;}
			.wp-admin #wpwrap{text-align:center}
			#wpwrap #wpcontent{display:flex;flex-direction:column;align-items:center;}
			#wpwrap ##wpbody-content{padding-bottom:0;float:none}
			#adminmenumain,#wpadminbar{}
			#wpfooter{display:none !important}
		}
		
		@media only screen and (min-width: 782px) {
			#messager.err{color:#f22424;padding:10px 10px;}#messager.ok{color:#11bd40;padding:10px 10px;}
			.img{width:100px;height:100px;padding:10px 10px;}
			.card-top{box-shadow: 2.5px 2.5px 5px 2.5px #C0C7CA;margin-top:32px;width:500px;height:424px;}
			.card-bottom{margin-top: 15px;display:flex;flex-direction:column;align-items:center;padding:10px 10px;background-color:#C0C7CA;width:480px;}
			.permalink{color:red;font-size:15px;padding:10px 10px;}
			.notice,.updated{display:none !important}
			.wp-admin{background-color:#fff !important}
			.metanotify-activation-page{margin:0 !important}
			.metanotify-activation-page h1{font-weight:600;font-size:28px}
			.metanotify-activation-page form{display:flex;flex-direction:column;align-items:center;}
			.metanotify-activation-page form label{display:block;margin-bottom:2px}
			.metanotify-activation-page form input[type="email"]{width:100%;padding:10px 10px}
			.metanotify-activation-page form .button{padding:10px 10px;text-transform:uppercase;margin-bottom:12px;margin-top:12px; width:250px;}
			.metanotify-activation-page form .to-dashboard{text-decoration:none}
			.metanotify-activation-page h1,.metanotify-activation-page p{padding:10px 10px;}
			.wp-admin #wpwrap{text-align:center}
			#wpwrap #wpcontent{display:flex;flex-direction:column;align-items:center;}
			#wpwrap ##wpbody-content{padding-bottom:0;float:none}
			#adminmenumain,#wpadminbar{}
			#wpfooter{display:none !important}
		}
        ');

		wp_localize_script(
			'jquery-core',
			'metanotify',
			array(
				'nonce' => wp_create_nonce('m3t4n0t1fy'),
				'ajaxURL' => admin_url('admin-ajax.php'),
				'adminURL' => admin_url(),
				'pluginVer' => META_NOTIFY_VER,
				'pluginUri' => META_NOTIFY_URI,
				'tosRequired' => __('You must accept our Terms & Conditions!', 'meta-notify')
			)
		);

		wp_enqueue_script('metanotify_admin', META_NOTIFY_URI . 'assets/js/admin.min.js', [], META_NOTIFY_VER, true);
	}
}

// Initialize the Singleton.
MetanotifyLicenseManager::init();