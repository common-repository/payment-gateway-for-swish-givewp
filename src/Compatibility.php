<?php

namespace GiveSwish;

class Compatibility
{
    public static function check()
    {
        add_action('admin_init', [__CLASS__, 'check_give_active']);
        add_action('admin_init', [__CLASS__, 'check_php_version']);
    }

    public static function is_incompatible($donation_data)
    {
        // write a function to process the payment
    }

    // check if give plugin is active else show error notice
    public static function check_give_active()
    {
        if (!is_plugin_active('give/give.php')) {
            add_action('admin_notices', [__CLASS__, 'give_inactive_notice']);
        }
    }

    public static function give_inactive_notice()
    {
        $class = 'notice notice-error';
        $message = __('GiveSwish requires GiveWP plugin to be active.', 'giveswish');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }

    // check if php version is compatible
    public static function check_php_version()
    {
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            add_action('admin_notices', [__CLASS__, 'php_version_notice']);
        }
    }

    public static function php_version_notice()
    {
        $class = 'notice notice-error';

        $message = __('GiveSwish requires PHP version 8.1.0 or higher. Your site is running on PHP' . PHP_VERSION, 'giveswish');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
}
