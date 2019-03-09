<?php

class ansAgendaConfig {

	public static $paypal_client_id = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
	public static $paypal_secret_id = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

	private static $paypal_dev_return_url = "http://example.dev/ok/";
	private static $paypal_dev_cancel_url = "http://example.dev/ko/";
	private static $paypal_pro_return_url = "http://example.com/ok/";
	private static $paypal_pro_cancel_url = "http://example.com/ko/";

	public static function getPaypalReturnUrl() {
		return ($_SERVER['SERVER_NAME'] === "example.com") ? self::$paypal_pro_return_url : self::$paypal_dev_return_url;
	}

	public static function getPaypalCancelUrl() {
		return ($_SERVER['SERVER_NAME'] === "example.dev") ? self::$paypal_pro_cancel_url : self::$paypal_dev_cancel_url;	
	}
}