<?php

namespace GiveSwish\Admin;

class Admin
{
    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'check_give_active']);
        add_filter('upload_mimes', [__CLASS__, 'allowed_files'], 100, 1);
    }

    public static function allowed_files($mimes)
    {
        $mimes['p12'] = 'application/x-pkcs12';
        $mimes['pem'] = 'application/x-pem-file';
        return $mimes;
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
}
