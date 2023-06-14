<?php

final class menu
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
			add_action('admin_menu', array($self, 'add_menu_page'));
		}
	}
	public function render($page_data)
	{}

    public function add_menu_page()
	{
		$this->hook_name = add_submenu_page('metanotify-tos', __('Customization', 'meta-notify'), __('Customization', 'meta-notify'), 'manage_options', 'meta-notify-settings', array($this, 'render'));
	}
}

menu::init();