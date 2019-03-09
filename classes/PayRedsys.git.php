<?php


class PayRedsys extends RedsysAPI {

	private $merchant_merchantcode = 999999999;
	private $terminal_number = 01;
	private $encrypted_key = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	private $currency = 978;
	private $transaction_type = 0;
	private $encrypted_type = 'HMAC_SHA256_V1';
	private $commerce_url = 'https://sis-t.redsys.es:25443/sis/realizarPago';
	private $merchant_url = 'http://example.com/tpv-response';
	private $merchant_url_ok = 'http://example.com/tpv-response-ok';
	private $merchant_url_ko = 'http://example.com/tpv-response-ko';
	private $params;
	private $signature;

	function __construct()
  {
  }

  public function payment($amount)
  {
  	$id=time();
  	parent::setParameter("DS_MERCHANT_MERCHANTCODE", $this->merchant_merchantcode);
	  parent::setParameter("DS_MERCHANT_CURRENCY", $this->currency);
	  parent::setParameter("DS_MERCHANT_TRANSACTIONTYPE", $this->transaction_type);
	  parent::setParameter("DS_MERCHANT_TERMINAL", $this->terminal_number);
	  parent::setParameter("DS_MERCHANT_MERCHANTURL", $this->merchant_url);
	  parent::setParameter("DS_MERCHANT_URLOK", $this->merchant_url_ok);    
	  parent::setParameter("DS_MERCHANT_URLKO", $this->merchant_url_ko);
	  parent::setParameter("DS_MERCHANT_ORDER",strval($id));
	  parent::setParameter("DS_MERCHANT_AMOUNT",$amount);

  	$this->params = parent::createMerchantParameters();
  	$this->signature = parent::createMerchantSignature($this->encrypted_key);
  }

  public function getParams()
  {
  	return $this->params;
  }

  public function getSignature()
  {
  	return $this->signature;
  }

  public function encryptedType()
  {
  	return $this->encrypted_type;
  }

  public function getComerceUrl()
  {
  	return $this->commerce_url;
  }

  public function getEncryptedKey()
  {
    return $this->encrypted_key;
  }
}