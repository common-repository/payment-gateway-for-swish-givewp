<?php

namespace GiveSwish;

use GiveSwish\Utility;
use Olssonm\Swish\Client;
use Olssonm\Swish\Payment;
use Olssonm\Swish\Callback;
use Olssonm\Swish\Exceptions\ValidationException;

class Swish
{
    public static  $client;
    public static $has_errors = false;
    public static $live = Client::PRODUCTION_ENDPOINT;
    public static $test = Client::TEST_ENDPOINT;

    public static function create(array $args)
    {
        // check if args is an array and contains all the required fields are set  use !isset
        if (!is_array($args) && count($args) < 1) {
            return false;
        }

        if (!isset($args['callbackUrl']) || !isset($args['payeePaymentReference']) || !isset($args['payeeAlias']) || !isset($args['payerAlias']) || !isset($args['amount']) || !isset($args['currency']) || !isset($args['message'])) {
            return false;
        }

        $client = new Client(
            self::get_certs(),
            self::endpoint()
        );
        // Create a new payment-object
        $payment = new Payment($args);
        $swish_errors = [];
        // Perform the request
        try {
            $response = $client->create($payment);
            //error_log($response->location);
            return $response;
        } catch (ValidationException $exception) {
            $errors = $exception->getErrors();
            foreach ($errors as $error) {
                $swish_errors['code'] =  $error->errorCode;
                // AM03
                $swish_errors['message'] = $error->errorMessage;
                // Invalid or missing Currency.
            }
            self::$has_errors = true;
            error_log('Swish Error: ' . json_encode($swish_errors));
            return $swish_errors;
        }
        //$id = $response->id;
        //$location = $response->location;
        //echo Utility::get_merchant_number();
        //error_log($location);
        //error_log(print_r((array)$response, true));
        //print_r((array)$new_client->get(new \Olssonm\Swish\Payment(['id' => $id])), true);
    }

    // get payment 

    public static function get_payment($id)
    {
        $new_client = new Client(
            self::get_certs(),
            self::endpoint()
        );
        return $new_client->get(new \Olssonm\Swish\Payment(['id' => $id]));
    }

    private static function get_test_certs()
    {
        return [
            GIVESWISH_PLUGIN_DIR . 'test-certs/Swish_Merchant_TestCertificate_1234679304.p12',
            'swish'
        ];
    }

    private static function get_certs()
    {
        if (self::get_mode() && self::get_mode() == 'sandbox') {
            return self::get_test_certs();
        }
        // production mode 
        //check if p12 file and password is set

        if (self::get_mode() && self::get_mode() == 'production') {

            // check if p12 file and password is set
            if (!Utility::get_certificate_file() || !Utility::get_certificate_password()) {
                return self::get_test_certs();
            }
            // return [
            //     Utility::get_certificate_file(),
            //     Utility::get_certificate_password()
            // ];
            return [
                //GIVESWISH_PLUGIN_DIR . 'certs/SwishCert.p12',
                Utility::media_url_to_path(Utility::get_certificate_file()),
                Utility::get_certificate_password(),
            ];
        }

        return self::get_test_certs();
    }

    public static function get_mode()
    {
        return \give_get_option('gswish_mode');
    }

    private static function endpoint()
    {
        if (self::get_mode() && self::get_mode() == 'production') {
            return self::$live;
        }
        return self::$test;
    }
}
