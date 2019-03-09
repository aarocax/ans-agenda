<?php

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;


class PayPaypal {

  private static function getContext() {

    $clientId = ansAgendaConfig::$paypal_client_id;
    $clientSecret = ansAgendaConfig::$paypal_secret_id;

    $apiContext = new ApiContext(
            new OAuthTokenCredential(
              $clientId,
              $clientSecret
            )
        );


    $apiContext->setConfig(
        array(
          'mode' => 'sandbox',
          'log.LogEnabled' => true,
          'log.FileName' =>  __DIR__.'/../logs/PayPal.log',
          'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
          'cache.enabled' => true,
          'cache.FileName' => __DIR__.'/../logs/PaypalCache'
      )
    );

    return $apiContext;
  }

  public static function payment($cart) {
    

    $apiContext = self::getContext();

    $return_url = ansAgendaConfig::getPaypalReturnUrl();
    $cancel_url = ansAgendaConfig::getPaypalCancelUrl();
    
    $payer = new Payer();
    $payer->setPaymentMethod("paypal");

    $item = new Item();
    $item->setName($cart['description'])
        ->setCurrency($cart['currency'])
        ->setQuantity($cart['quantity'])
        ->setSku("123123") // Similar to `item_number` in Classic API
        ->setPrice($cart['amount']);

    $itemList = new ItemList();
    $itemList->setItems(array($item));

    $details = new Details();
    $details->setShipping(0)
        ->setTax(0)
        ->setSubtotal($cart['amount']);


    $amount = new Amount();
    $amount->setCurrency($cart['currency'])
        ->setTotal($cart['amount'])
        ->setDetails($details);

    
    $transaction = new Transaction();
    $transaction->setAmount($amount)
        ->setItemList($itemList)
        ->setDescription("Payment description")
        ->setInvoiceNumber(uniqid());


    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl($return_url)
        ->setCancelUrl($cancel_url);

    $payment = new Payment();
    $payment->setIntent("sale")
        ->setPayer($payer)
        ->setRedirectUrls($redirectUrls)
        ->setTransactions(array($transaction));

    try {
      $result = $payment->create($apiContext);
    } catch (Exception $ex) {
      exit(1);
    }

    return $result;
  }

  public static function executePayment($paymentId, $PayerID) {

    $apiContext = self::getContext();

    $payment = Payment::get($paymentId, $apiContext);

    $execution = new PaymentExecution();
    $execution->setPayerId($PayerID);

    try {
      $result = $payment->execute($execution, $apiContext);
      try {
        $payment = Payment::get($paymentId, $apiContext);
      } catch (Exception $ex) {
        exit(1);
      }
    } catch (Exception $e) {
      exit(1);
    }

    return $payment;
  }

}