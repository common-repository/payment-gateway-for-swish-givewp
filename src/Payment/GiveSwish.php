<?php

namespace GiveSwish\Payment;

use GiveSwish\Plugin;
use GiveSwish\Admin\Admin;
use GiveSwish\Swish;
use GiveSwish\Utility;

class GiveSwish
{
    public function __construct()
    {
        Admin::init();
        Plugin::load();
        add_filter('give_payment_gateways', function ($gateways) {
            $gateways['swish'] = [
                'admin_label' => esc_html__('Swish', 'giveswish'),
                'checkout_label' => esc_html__('Swish', 'giveswish'),
            ];

            return $gateways;
        });

        add_filter('give_get_sections_gateways', function ($sections) {
            $sections['swish-settings'] = esc_html__('Swish', 'giveswish');

            return $sections;
        });

        add_action('give_swish_cc_form', [__CLASS__, 'swish_cc_form']);
        add_filter('give_get_settings_gateways', [__CLASS__, 'settings_gateways']);
        // add_filter('give_metabox_form_data_settings', [__CLASS__ 'swish_setting_tab']);
        add_filter('give_donation_form_submit_button', [__CLASS__, 'submit_button'], \PHP_INT_MAX, 2);
        add_action('give_gateway_swish', [__CLASS__, 'process_payment']);
        add_action('init', [__CLASS__, 'process_callback'], \PHP_INT_MAX);
        add_filter('give_payment_confirm_swish', [__CLASS__, 'give_swish_success_page_content']);
        // give make swish number field required
        add_filter('give_donation_form_required_fields', [__CLASS__, 'swish_number_required']);
        // enqueue css and js after the cc form 
        add_action('wp_enqueue_scripts', [__CLASS__, 'swish_front_scripts']);

        // after  summary add js 
        //  add_action('give_after_single_form_summary', [__CLASS__, 'swish_after_summary_js']);

        // check payment status ajax 
        add_action('wp_ajax_giveswish_check_payment_status', [__CLASS__, 'check_payment_status']);
        add_action('wp_ajax_nopriv_giveswish_check_payment_status', [__CLASS__, 'check_payment_status']);

        // update payment status ajax
        add_action('wp_ajax_giveswish_update_payment_status', [__CLASS__, 'update_payment_status']);
        add_action('wp_ajax_nopriv_giveswish_update_payment_status', [__CLASS__, 'update_payment_status']);
    }

    public static function check_payment_status()
    {
        // if nonce is not valid
        // if (!wp_verify_nonce($_POST['nonce'], 'give_swish_nonce')) {
        //     $response = [
        //         'status' => 'error',
        //         'message' => 'Nonce not valid',
        //         'uri' => give_get_failed_transaction_uri(),
        //     ];
        //     echo json_encode($response);
        // }
        // check if payment is completed 
        $payment_id = sanitize_text_field($_POST['payment_id']);
        // if payment id is not set or null get session payment id
        if (strlen($payment_id) < 32) {
            $payment_id =
                give_get_purchase_session()['purchase_key'];
        }
        // if payment id is empty or not string
        $uri_back_to_checkout = give_get_failed_transaction_uri();
        if (empty($payment_id) || !is_string($payment_id)) {
            $response = [
                'status' => 'error',
                'message' => 'Payment Failed ! Payment id not valid',
                'uri' => $uri_back_to_checkout,
            ];
            echo json_encode($response);
        }
        // get give payment by purchase key 
        $payment = give_get_payment_by('key', $payment_id);
        // if payment is not found
        if (!$payment) {
            $response = [
                'status' => 'error',
                'message' => 'Payment Failed ! Payment not found',
                'uri' => $uri_back_to_checkout,
            ];
            echo json_encode($response);
        }
        $payment_status = $payment->status;
        if ($payment_status == 'publish') {
            $uri = give_get_success_page_uri();
            $response = [
                'status' => 'success',
                'message' => 'Payment completed',
                'uri' => $uri,
            ];
            echo json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Payment Failed ! Payment not completed',
                'uri' => $uri_back_to_checkout,
            ];
            echo json_encode($response);
        }
        wp_die();
    }

    public static function update_payment_status()
    {
        // if nonce is not valid
        // if (!wp_verify_nonce($_POST['nonce'], 'give_swish_nonce')) {
        //     $response = [
        //         'status' => 'error',
        //         'message' => 'Nonce not valid',
        //         'uri' => give_get_failed_transaction_uri(),
        //     ];
        //     echo json_encode($response);
        // }
        // check if payment is completed 
        $payment_id = sanitize_text_field($_POST['payment_id']);
        // if payment id is not set or null get session payment id
        if (strlen($payment_id) < 32) {
            $payment_id =
                give_get_purchase_session()['purchase_key'];
        }
        $status =  isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        // if $status is empty or not string
        if (empty($status) || !is_string($status)) {
            $response = [
                'status' => 'error',
                'message' => 'Update Failed ! Invalid Status',
                'uri' => give_get_failed_transaction_uri(),
            ];
            echo json_encode($response);
        }
        // if payment id is empty or not string
        $uri_back_to_checkout = give_get_failed_transaction_uri();

        if (empty($payment_id) || !is_string($payment_id)) {
            $response = [
                'status' => 'error',
                'message' => 'Update Failed ! Payment id not valid',
                'uri' => $uri_back_to_checkout,
            ];
            echo json_encode($response);
        }
        // get give payment by purchase key 
        $payment = give_get_payment_by('key', $payment_id);
        // if payment is not found
        if (!$payment) {
            $response = [
                'status' => 'error',
                'message' => 'Update Failed ! Payment not found',
                'uri' => $uri_back_to_checkout,
            ];
            echo json_encode($response);
        }
        // update payment status
        if (!empty($status) && $payment->update_status($status)) {
            $uri = $status == 'publish'  ? give_get_success_page_uri() : give_get_failed_transaction_uri();
            $response = [
                'status' => 'success',
                'message' => 'Successfully updated payment status to ' . $status,
                'uri' => $uri,
            ];
            echo json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Payment Failed ! Payment not completed',
                'uri' => $uri_back_to_checkout,
            ];
            echo json_encode($response);
        }
        wp_die();
    }

    public static function settings_gateways($settings)
    {
        if ('swish-settings' !== give_get_current_setting_section()) {
            return $settings;
        }

        $global_settings = [

            // section 
            [
                'id' => 'give_title_swish_settings',
                'type' => 'title',
            ],
            [
                'name' => esc_html__('Swish Mode', 'giveswish'),
                'desc' => esc_html__('Click "Sandbox" to allow testing Swish without credentials.', 'giveswish'),
                'id' => 'gswish_mode',
                'type' => 'radio_inline',
                'default' => 'production',
                'options' => [
                    'production' => esc_html__('Production', 'giveswish'),
                    'sandbox' => esc_html__('Sandbox', 'giveswish'),
                ],
            ],
            [
                'name' => esc_html__('Live Certificate( p12 file ) ', 'giveswish'),
                'desc' => esc_html__('Live Certificate( p12 file )', 'giveswish'),
                'id' => 'gswish_p12_file',
                'type' => 'file',
            ],
            [
                'name' => esc_html__('Live Certificate Password', 'giveswish'),
                'desc' => __('Password for the <pre>SwishCert.p12</pre> file', 'giveswish'),
                'id' => 'gswish_p12_password',
                'type' => 'password',
            ],
            [
                'name' => esc_html__('Merchant Swish Number', 'giveswish'),
                'desc' => esc_html__('Enter your merchant Swish Number', 'giveswish'),
                'id' => 'gswish_number',
                'type' => 'number',
            ],
            [
                'type' => 'sectionend',
                'id' => 'swish_title_live',
            ]

        ];

        return array_merge($settings, $global_settings);
    }

    public static function swish_cc_form($form_id)
    {
        do_action('giveswish_before_cc_fields', $form_id);
        // get logo from assets 
        $swish_logo = '';
        $swish_logo = apply_filters('give_swish_logo', $swish_logo);
?>
        <style>
            .sgp-form-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
        </style>

        <div class="sgp-form-container">
            <div class="give-swish-logo"><img src="<?php echo  esc_url(GIVESWISH_PLUGIN_URL . 'assets/images/swish-logo.svg'); ?>" alt="Swish" width="150" height="150" /></div>
            <div class="give-swish-phone-number">
                <input type="tel" name="give_swish_phone_number" id="give-swish-phone-number" placeholder="Enter your swish number" required>
            </div>
            <h3 class="gswish-payment-description">Pay seamlessly with Swish App</h3>
        </div>
<?php
        return true;
    }


    public static function swish_front_scripts($form_id)
    {
        // get the js from assets 
        $js = file_get_contents(GIVESWISH_PLUGIN_DIR . 'assets/js/giveswish.js');
        $css = file_get_contents(GIVESWISH_PLUGIN_DIR . 'assets/css/swish-front.css');
        wp_add_inline_script('give-donation-summary-script-frontend', $js, 'after');
        wp_add_inline_style('give-styles', $css, 'after');
    }

    public static function swish_after_summary_js($form_id)
    {
        // get the js from assets 
        echo '<script type="text/javascript">' . file_get_contents(GIVESWISH_PLUGIN_DIR . 'assets/js/giveswish.js') . '</script>';
    }

    public static function swish_number_required($required_fields)
    {
        $required_fields['give_swish_phone_number'] = [
            'error_id' => 'invalid_swish_number',
            'error_message' => __('Please enter a valid swish number', 'giveswish'),
        ];
        return $required_fields;
    }

    public static function submit_button($button, $form_id)
    {
        if ('swish' !== give_get_chosen_gateway($form_id)) {
            return $button;
        }
        $button = '<input type="submit" name="give-purchase" id="give-purchase-button" class="give-submit give-btn give-btn-green give-btn-lg giveswish-submit" value="Pay with Swish" />';
        do_action('giveswish_after_submit_button', $form_id);
        return $button;
    }


    public static function process_payment($posted_data)
    {
        // Make sure we don't have any left over errors present.
        give_clear_errors();

        // Any errors?
        $errors = give_get_errors();

        // No errors, proceed.
        if (!$errors) {

            $form_id         = intval($posted_data['post_data']['give-form-id']);
            $price_id        = !empty($posted_data['post_data']['give-price-id']) ? $posted_data['post_data']['give-price-id'] : 0;
            $donation_amount = !empty($posted_data['price']) ? $posted_data['price'] : 0;
            $donor_swish_number =  !empty($posted_data['post_data']['give_swish_phone_number']) ? $posted_data['post_data']['give_swish_phone_number'] : 0;
            // Setup the payment details.
            $donor_swish_number = Utility::format_swish_number($donor_swish_number);
            if (!$donor_swish_number) {
                give_set_error('invalid_swish_number', __('Please enter a valid phone number ( Must be a 10 digits numeric with leading zero)', 'giveswish'));
                give_send_back_to_checkout('?payment-mode=swish');
            }
            $donation_data = array(
                'price'           =>  $donation_amount,
                'give_form_title' => $posted_data['post_data']['give-form-title'],
                'give_form_id'    => $form_id,
                'give_price_id'   => $price_id,
                'date'            => $posted_data['date'],
                'user_email'      => $posted_data['user_email'],
                'purchase_key'    => $posted_data['purchase_key'],
                'currency'        => give_get_currency($form_id),
                'user_info'       => $posted_data['user_info'],
                'status'          => 'pending',
                'gateway'         => 'swish',
            );

            // Record the pending donation.
            $donation_id = give_insert_payment($donation_data);

            if (!$donation_id) {
                give_record_gateway_error(
                    __('Swish Error', 'giveswish'),
                    sprintf(
                        /* translators: %s Exception error message. */
                        __('Unable to create a pending donation with Give.', 'giveswish')
                    )
                );
                give_set_error('swish_error', __('Unable to start donation', 'giveswish'));
                give_send_back_to_checkout('?payment-mode=swish');
                return;
            }

            $callback_url = $posted_data['post_data']['give-current-url'];
            $callback_url = add_query_arg('dID', $donation_id, $callback_url);
            //error_log('callback_url ' . $callback_url);
            $payment_data = [
                'payeePaymentReference' => $donation_id,
                'callbackUrl' => $callback_url,
                'payerAlias' => $donor_swish_number,
                'payeeAlias' => Utility::get_merchant_number(),
                'amount' => floatval($donation_amount),
                'currency' => 'SEK',
                'message' => 'Donation to ' . $posted_data['post_data']['give-form-title']
            ];
            // log the payment data in json format 
            error_log(json_encode($payment_data));
            $response = Swish::create($payment_data);
            if (!Swish::$has_errors) {
                give_update_payment_status($donation_id, 'processing');
                $id = $response->id;
                give_update_meta($donation_id, '_give_swish_cpayment_id', $id);
            } else {
                give_record_gateway_error(
                    __('Swish Payment Error', 'giveswish'),
                    sprintf(
                        /* translators: %s Exception error message. */
                        __('Unable to  process payment.', 'giveswish')
                    )
                );
                give_set_error('swish_error', __('Swish could not process your payment', 'giveswish'));
                give_send_back_to_checkout('?payment-mode=swish');
                return;
            }
        } else {
            // set error 
            give_set_error('swish_error', __('Error processing donation', 'giveswish'));
            give_send_back_to_checkout('?payment-mode=swish');
        }
    }

    public static function process_callback()
    {
        $donation_id = isset($_GET['dID']) ? sanitize_text_field($_GET['dID']) : 0;
        // return if donation id is not set
        if (!$donation_id) {
            return false;
        }
        // get the meta
        $payment_id = give_get_meta($donation_id, '_give_swish_cpayment_id', true);

        // if payment id is not set return
        if (!$payment_id) {
            return false;
        }
        // get the payment
        $payment = Swish::get_payment($payment_id);
        // check if payment is paid 
        error_log('DID Payment status of  ' . $payment_id . ' : ' . $payment->status);
        $gpayment = new \Give_Payment($donation_id);
        // if paid update donation status to complete
        if ($payment->status == 'PAID') {
            $gpayment->update_status('publish');
            give_send_to_success_page();
            return true;
        } else {
            $gpayment->update_status('failed');
            give_send_back_to_checkout('?payment-mode=swish&error=payment_failed');
            return false;
        }
    }

    public static function give_swish_success_page_content($content)
    {
        // create an anivated svg checkmark
        $checkmark = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 52 52"><circle class="circle" cx="26" cy="26" r="25" fill="none"/><path class="check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>';
        $checkmark = apply_filters('give_swish_checkmark', $checkmark);
        // prepend to content 
        $content = $checkmark . PHP_EOL . $content;
        $content = '<h3>Thank you for your donation!</h3>';
        $content .= '<p>Thank you for your donation. Your transaction has been completed, and a receipt for your purchase has been emailed to you. You may log into your account at Swish App  to view details of this transaction.</p>';
        return $content;
    }
}
