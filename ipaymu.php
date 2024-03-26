<?php
/**
 * WHMCS iPaymu Payment Gateway Module
 *
 * iPaymu Payment Gateway modules allow you to integrate iPaymu Web with the
 * WHMCS platform.
 *
 * For more information, please refer to the online documentation.
 * @see https://documenter.getpostman.com/view/7508947/SWLfanD1?version=latest
 *
 * Module developed based on official WHMCS Sample Payment Gateway Module
 * 
 * @author syaifudin@ipaymu.com
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
require_once(dirname(__FILE__) . '/ipaymu/Helper.php');

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 *
 * @return array
 */
function ipaymu_MetaData()
{
    return array(
        'DisplayName' => 'Direct Payment Gateway Module',
        'APIVersion' => '1.0', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => true,
    );
}
/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function ipaymu_config()
{
    return array(
            'FriendlyName' => array(
                'Type' => 'System',
                'Value' => 'iPaymu Direct Payment',
            ),
        
            'mode' => array(
                'FriendlyName' => 'Mode',
                'Type' => 'dropdown',
                'Options' => array(
                    'production' => 'Production',
                    'sandbox' => 'Sandbox',
                ),
                'Description' => '<small>Use Mode Sandbox for Development Mode</small>',
            ),
            'ipaymu_va' => array(
                'FriendlyName' => 'VA Live/Production',
                'Type' => 'password',
                'Size' => '50',
                'Default' => '',
                'Description' => '<small>Dapatkan VA Production <a href="https://my.ipaymu.com/integration" target="_blank">di sini</a></small>',
            ),
            'ipaymu_apikey' => array(
                'FriendlyName' => 'API Key Live/Production',
                'Type' => 'password',
                'Size' => '50',
                'Default' => '',
                'Description' => '<small>Dapatkan API Key Production <a href="https://my.ipaymu.com/integration" target="_blank">di sini</a></small>',
            ),
            'ipaymu_va_sandbox' => array(
                'FriendlyName' => 'VA Sandbox',
                'Type' => 'password',
                'Size' => '50',
                'Default' => '',
                'Description' => '<small>Dapatkan VA Sandbox <a href="https://sandbox.ipaymu.com/integration" target="_blank">di sini</a></small>',
            ),

            'ipaymu_apikey_sandbox' => array(
                'FriendlyName' => 'API Key Sandbox',
                'Type' => 'password',
                'Size' => '50',
                'Default' => '',
                'Description' => '<small>Dapatkan API Key Sandbox <a href="https://sandbox.ipaymu.com/integration" target="_blank">di sini</a></small>',
            ),

            'ipaymu_expired' => array(
                'FriendlyName' => 'Expiry Period',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '24',
                'Description' => '<br><small>The validity period of the transaction before it expires. Max 24 in hour.</small>',
            ),        
    );
}
/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return string
 */
function ipaymu_link($params)
{
    if ($params['mode'] == 'sandbox') {
        $va = $params['ipaymu_va_sandbox'];
        $key = $params['ipaymu_apikey_sandbox'];
        $base = 'https://sandbox.ipaymu.com';
    }else{
        $va = $params['ipaymu_va'];
        $key = $params['ipaymu_apikey'];
        $base = 'https://my.ipaymu.com';
    }

    $expired = $params['ipaymu_expired'];

    $orderId = $params['invoiceid'];

    $name = $params['clientdetails']['firstname'].' '.$params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];

    $product[] = $params['description'];
    $price[] = intval($params['amount']);
    $qty[] = 1;
    
	$systemUrl = $params['systemurl'];

    $returnUrl = $systemUrl."/modules/gateways/callback/ipaymu_return.php";
    $callbackUrl = $systemUrl."/modules/gateways/callback/ipaymu_callback.php?order_id=$orderId";

    $ipaymu = new Helper($base, $va, $key);
    $returnUrl = $params['returnurl'];

    $createLink = $ipaymu->create($product, $qty, $price, $name, $phone, $email, $returnUrl, $callbackUrl);

	$img        = $systemUrl . "/modules/gateways/ipaymu/logo.png"; 
    $htmlOutput = '</br><img style="width: 152px;" src="' . $img . '" alt="iPaymu Direct Payment">';
    $message    = null;

    if (!empty($createLink['err'])) {
        $message = '<p class="text-danger"> Invalid Response from iPaymu. Please contact support@ipaymu.com</p>';
    }

    if (empty($createLink['res'])) {
        $message = '<p class="text-danger"> Request Failed: Invalid Response from iPaymu. Please contact support@ipaymu.com</p>';
    }

    if (empty($createLink['res']['Data']['Url'])) {
        $message = '<p class="text-danger"> Invalid request. Response iPaymu '.$createLink['res']['Message'].'</p>';
    }

    if ($message == null) {
        $url = $createLink['res']['Data']['Url'];
        $htmlOutput .= '<a href="'.$url.'" class="btn btn-default btn-sm" > Bayar </a>';
    }else{
        $htmlOutput .= $message;    
    }

    return $htmlOutput;

}