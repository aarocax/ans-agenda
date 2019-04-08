<?php

function getUTCOffset($timezone, $number = true, $colon=true) {
  $dtz = new DateTimeZone($timezone);
  $offset = $dtz->getOffset(new DateTime());
  if ($number) {
  	$result = $offset/3600;
  } else {
  	$format=($colon)?'%+03d:%02u':'%+03d%02u';
  	$result = sprintf($format, $offset / 3600, abs($offset) % 3600 / 60);
  }
  return $result;
}

function hoursAvailable($offset, $hours) {
	$new_hours = [];
	foreach ($hours as $key => $hour) {
		if ( (mb_substr($hour, 0, 2) + $offset) < 0 ) {
			$new_hours[$key] = 24 + (mb_substr($hour, 0, 2) + $offset).":00:00";
		} else {
			$new_hours[$key] = mb_substr($hour, 0, 2) + $offset.":00:00";
		}
	}
	return $new_hours;
}

function ans_agenda_ajax_get_timezone() {

	$timezones = include dirname( __FILE__ ) . '/inc/timezones_list.php';
	$nonce = $_POST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
		die (' ');
	}
	$country_code = $_POST['country_code'];
	if (is_array($timezones[$country_code])) {
		echo json_encode($timezones[$country_code], JSON_FORCE_OBJECT);
	} else {
		echo json_encode([$timezones[$country_code]], JSON_FORCE_OBJECT);
	};

	die();
}

add_action('wp_ajax_ans_agenda_ajax_get_timezone', 'ans_agenda_ajax_get_timezone');
add_action('wp_ajax_nopriv_ans_agenda_ajax_get_timezone', 'ans_agenda_ajax_get_timezone' );

function ans_agenda_ajax_show_calendar() {

	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
		die (' ');
	}

	$country_timezone = $_POST['country_timezone'];
	$date = date( "Y-m-d", strtotime($_POST['date']) );


	$offset = getUTCOffset($country_timezone);
	$offset_local = getUTCOffset("Europe/Madrid");
	$offset = $offset - $offset_local;
	$hours = ["10:00:00","11:00:00","12:00:00","13:00:00","14:00:00","15:00:00","16:00:00","17:00:00","18:00:00","19:00:00","20:00:00"];
	$hours = hoursAvailable($offset, $hours);

	// get reserved hours
	$ansappDBA = new ansAgendaDBA;
	$appointments = $ansappDBA->getFreeHours( $date );
	$reserved_hours = array();
	foreach ($appointments as $key => $appointment) {
		$reserved_hours[] = $appointment->hour;
	}

	$reserved_hours = hoursAvailable($offset, $reserved_hours);

	$tds = "";

	if ($offset < 0) {
		$offset = abs($offset);
	} else {
		$offset = $offset * -1;
	}

	foreach ($hours as $key => $hour) {
		if (!in_array($hour, $reserved_hours)) {
	    $tds .= '<a class="list-group-item list-group-item-action" id="'.((int)mb_substr($hour, 0, 2) + $offset).':00:00" href="" title="">'.substr($hour,0,-3).'</a>';
		} else {
			$tds .= '<p class="list-group-item list-group-item-action disabled">'.substr($hour,0,-3).'</p>';
		}
	}

	echo $tds;
	
	exit;
}

add_action('wp_ajax_ans_agenda_ajax_show_calendar', 'ans_agenda_ajax_show_calendar');
add_action('wp_ajax_nopriv_ans_agenda_ajax_show_calendar', 'ans_agenda_ajax_show_calendar' );

function ans_agenda_ajax_save_form() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
		die (' ');
	}

	$ansAgendaDBA = new ansAgendaDBA;

	$date = new DateTime('now', new DateTimeZone('Europe/Madrid'));

	$appointment = array(
		'creation_date' 		=> $date->format('Y-m-d H:i:s'),
		'payment_id'				=> $_POST['payment_id'],
		'name' 							=> $_POST['name'],
		'country' 					=> $_POST['country'],
		'email'     				=> $_POST['email'],
		'phone'     				=> $_POST['phone'],
		'date'      				=> date( "Y-m-d H:i:s", strtotime($_POST['date']) ),
		'hour'							=> $_POST['hour'],
		'customer_hour'			=> $_POST['customer_hour'],
		'customer_timezone'	=> $_POST['customer_timezone'],
		'amount'						=> $_POST['amount'],
		'pay_mode'  				=> $_POST['pay_mode'],
		'paid'							=> false
	);

	echo $ansAgendaDBA->insert($appointment);

	die();
}

add_action('wp_ajax_ans_agenda_ajax_save_form', 'ans_agenda_ajax_save_form');
add_action('wp_ajax_nopriv_ans_agenda_ajax_save_form', 'ans_agenda_ajax_save_form' );


function ansapp_ajax_redsys_pay() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
		die (' ');
	}
	$contribution = $_POST['amount'];
	$redsys = new PayRedsys;
	$redsys->payment($contribution*100);

	$form  = '<form id="redsys-form" name="frm" action="http://redys.com" method="POST">';
	$form .=  '<input type="hidden" name="Ds_SignatureVersion" value="'.$redsys->getSignature().'">';
	$form .=  '<input type="hidden" name="Ds_MerchantParameters" value="'.$redsys->getParams().'">';
	$form .=  '<input type="hidden" name="Ds_Signature" value="'.$redsys->getSignature().'">';
	$form .=  '<div class="row">';
	$form .=   '<div class="col-2">';
	$form .=    '<input id="return-form" type="button" class="btn btn-secondary" value="Volver">';
	$form .=   '</div>';
	$form .=   '<div class="col-4">';
	$form .=    '<input type="submit" class="btn btn-orange" id="redsys-pay-submit" value="Pagar">';
	$form .=   '</div>';
	$form .=  '</div>';
	$form .= '</form>';

	echo $form;

	die();
}

add_action('wp_ajax_ansapp_ajax_redsys_pay', 'ansapp_ajax_redsys_pay');
add_action('wp_ajax_nopriv_ansapp_ajax_redsys_pay', 'ansapp_ajax_redsys_pay' );


function ansapp_ajax_paypal_pay() {

	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
		die (' ');
	}

	$amount = $_POST['amount'];

	$cart = array(
		'amount' => $amount,
		'description' => 'servicio tarot',
		'currency' => 'EUR',
		'quantity' => 1,
	);

	$payment = PayPaypal::payment($cart);

	$transactions = $payment->getTransactions();

  $transaction = $transactions[0];

  $approvalUrl = $payment->getApprovalLink();

  $form  = '{"form":"';
	$form .= '<form id=\"\" name=\"frm\" action=\"\" method=\"POST\">';
	$form .=  '<div class=\"row\">';
	$form .=   '<div class=\"col-2\">';
	$form .=    '<input id=\"return-form\" type=\"button\" class=\"btn btn-secondary\" value=\"Volver\">';
	$form .=   '</div>';
	$form .=   '<div class=\"col-4\">';
	$form .=    '<a href=\"'.$payment->getApprovalLink().'\" class=\"btn btn-orange\" id=\"paypal-pay-submit\">Pagar</a>';
	$form .=   '</div>';
	$form .=  '</div>';
	$form .= '</form>",';
	$form .= '"payment_id":"'.$payment->getId().'"}';

	echo $form;

	exit();
}

add_action('wp_ajax_ansapp_ajax_paypal_pay', 'ansapp_ajax_paypal_pay');
add_action('wp_ajax_nopriv_ansapp_ajax_paypal_pay', 'ansapp_ajax_paypal_pay' );

function ansapp_ajax_transferencia_pay() {

	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
		die (' ');
	}

	$amount = $_POST['amount'];

	

	echo $form;

	exit();
}

add_action('wp_ajax_ansapp_ajax_transferencia_pay', 'ansapp_ajax_transferencia_pay');
add_action('wp_ajax_nopriv_ansapp_ajax_transferencia_pay', 'ansapp_ajax_transferencia_pay' );


