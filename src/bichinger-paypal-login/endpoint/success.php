<?php

// index.php is a frontend script
define('TL_MODE', 'FE');
// Start the session so we can access known request tokens
@session_start();

// Allow to bypass the token check
if (!isset($_POST['REQUEST_TOKEN'])) {
    define('BYPASS_TOKEN_CHECK', true);
}


// Initialize the system
require('../../../../system/initialize.php');

// load language files
\System::loadLanguageFile('tl_paypal_login_settings');

// get parameters
$paymentId = \Input::get('paymentId');
$token = \Input::get('token');
$PayerID = \Input::get('PayerID');

if (!empty($paymentId) && !empty($token) && !empty($PayerID)) {

    try {
        // complete payment
        $payment = \Bichinger\PayPalLogin\Endpoint::handlePayment($paymentId, $token, $PayerID);
        // complete registration
        \System::log(sprintf($GLOBALS['TL_LANG']['MSC']['payment_approved'], $payment->getId()), __METHOD__, TL_GENERAL);
        // redirect
        \Bichinger\PayPalLogin\Paygate::approveMember($payment);

    } catch (\Bichinger\PayPalLogin\Exception\PaymentException $e) {
        // redirect to configured error page
        \System::log(sprintf($GLOBALS['TL_LANG']['MSC']['payment_exception_error'], $e->getMessage()), __METHOD__, TL_ERROR);
        $url = \Bichinger\Helper\UrlHelper::getUrlByPageId($settings->getRedirectAfterError());
        header('Location: ' . $url);
        exit();
    } catch (\Bichinger\PayPalLogin\Exception\MemberForPaymentNotFoundException $e) {
        // redirect to configured error page
        \System::log(sprintf($GLOBALS['TL_LANG']['MSC']['member_not_found_exception_error'], $e->getPayment()->getId()), __METHOD__, TL_ERROR);
        $url = \Bichinger\Helper\UrlHelper::getUrlByPageId($settings->getRedirectAfterError());
        header('Location: ' . $url);
        exit();
    }
}

exit();