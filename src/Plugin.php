<?php

namespace GiveSwish;

use GiveSwish\Compatibility;

class Plugin
{

    public static function load()
    {
        Compatibility::check();
        // activator and deactivtor 
        register_activation_hook(GIVESWISH_PLUGIN_FILE, [__CLASS__, 'activate']);
        register_deactivation_hook(GIVESWISH_PLUGIN_FILE, [__CLASS__, 'deactivate']);
        add_filter('plugin_action_links_' . GIVESWISH_PLUGIN_BASENAME, [__CLASS__, 'setting_links']);
        add_filter('plugin_row_meta', [__CLASS__, 'paid_support'], 10, 2);
        add_action('wp_print_scripts', [__CLASS__, 'enqueue_scripts']);
        // localize give_swish_nonce , $payment_id
        add_action('wp_enqueue_scripts', [__CLASS__, 'localize_script']);
    }

    public static function localize_script()
    {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode(
            $post->post_content,
            'give_form'
        ) || is_singular('give_forms')) {
            // register and enqueue ajax js 
            wp_register_script('gswish-ajax', GIVESWISH_PLUGIN_URL . 'assets/js/gswish-ajax.js', ['jquery'], GIVESWISH_PLUGIN_VERSION, true);
            wp_enqueue_script('gswish-ajax');
            $payment_id = give_get_purchase_session()['purchase_key'];
            error_log(json_encode(give_get_purchase_session()));
            $nonce = wp_create_nonce('give_swish_nonce');
            wp_localize_script('gswish-ajax', 'giveswish', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => $nonce,
                'payment_id' => $payment_id
            ]);
        }
    }

    public static function enqueue_scripts()
    {

        // register magnific popup script and styles  and giveswish.js
        wp_register_script('gswish-popup', GIVESWISH_PLUGIN_URL . 'assets/js/gswish-popup.min.js', ['jquery'], GIVESWISH_PLUGIN_VERSION, true);
        wp_register_style('gswish-popup', GIVESWISH_PLUGIN_URL . 'assets/css/gswish-popup.css', [], GIVESWISH_PLUGIN_VERSION);
        wp_register_script('giveswish', GIVESWISH_PLUGIN_URL . 'assets/js/giveswish.js', ['jquery'], GIVESWISH_PLUGIN_VERSION, true);

        if (is_singular('give_forms')) {
            // enqueue 
            wp_enqueue_script('giveswish');
            wp_enqueue_script('gswish-popup');
            wp_enqueue_style('gswish-popup');
        }
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode(
            $post->post_content,
            'give_form'
        )) {

            wp_enqueue_script('giveswish');
            wp_enqueue_script('gswish-popup');
            wp_enqueue_style('gswish-popup');
        }
    }

    public static function activate()
    {
        // do something 
    }

    public static function deactivate()
    {
        // do something 
    }

    public static function setting_links($links)
    {   // add buy me a coffe 
        $links[] = '<a href="https://www.buymeacoffee.com/proloybhaduri" style="color:green;" target="_blank">' . __('Buy me a coffee', 'giveswish') . '</a>';
        $links[] = '<a href="' . admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=swish-settings') . '">' . __('Settings', 'giveswish') . '</a>';
        return array_reverse($links);
    }

    public static function paid_support($links, $file)
    {
        if ($file == GIVESWISH_PLUGIN_BASENAME) {
            $links[] = '<a style="color:green;font-weight:650;" href="mailto:support@proloybhaduri.com" target="_blank">' . __('Get Help', 'giveswish') . '</a>';
        }
        return $links;
    }
}
