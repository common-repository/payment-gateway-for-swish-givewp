<?php

namespace GiveSwish;

class Utility
{
    /**
     * Merchant number
     *
     * @return mixed
     */

    public static function get_merchant_number()
    {
        if (empty(\give_get_option('gswish_number'))) {
            return false;
        }
        return \give_get_option('gswish_number');
    }

    /**
     * Get merchant certificate password 
     *
     * @return mixed
     */
    public static function get_certificate_password()
    {
        if (empty(\give_get_option('gswish_p12_password'))) {
            return false;
        }
        return \give_get_option('gswish_p12_password');
    }

    public static function get_certificate_file()
    {
        if (empty(\give_get_option('gswish_p12_file'))) {
            return false;
        }
        return \give_get_option('gswish_p12_file');
    }

    public static function format_swish_number($number)
    {
        if (strlen($number) != 10) {
            return false;
        }
        if (!is_numeric($number)) {
            return false;
        }
        if (substr($number, 0, 1) != '0') {
            return false;
        }
        $number = substr($number, 1);
        $number = '46' . $number;
        return $number;
    }

    public static function media_url_to_path($url)
    {
        $upload_dir = \wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        $file_path = str_replace($base_url, $upload_dir['basedir'], $url);
        return $file_path;
    }
}
