<?php
/*
Plugin Name: Ans Agenda
Plugin URI: 
Description: Agenda para citas
Version: 1.0.0
Author: anselmo aroca
*/

require_once __DIR__ . '/vendor/autoload.php';

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* initialization / install */
include_once dirname( __FILE__ ) . '/classes/ansAgendaConfig.php';
include_once dirname( __FILE__ ) . '/classes/ansAgendaMain.php';
include_once dirname( __FILE__ ) . '/classes/ansAgendaDBA.php';
include_once dirname( __FILE__ ) . '/classes/geoplugin.class.php';
include_once dirname( __FILE__ ) . '/classes/RedsysAPI.php';
include_once dirname( __FILE__ ) . '/classes/PayRedsys.php';
include_once dirname( __FILE__ ) . '/classes/PayPaypal.php';
include_once dirname( __FILE__ ) . '/ans-agenda-ajax-calls.php';


// $payss = new PayRedsys();

$ans_agenda_plugin = new ansAgendaMain;

register_activation_hook(__FILE__, array($ans_agenda_plugin,'install') ); 

function ansapp_scripts() {

	//wp_enqueue_style( 'jquery-ui-css', plugins_url( '/js/jquery-ui-1.12.1/jquery-ui.min.css', __FILE__ ), false,'1.1','all');

	wp_enqueue_style( 'jquery-ui-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', false,'1.1','all');

	//wp_enqueue_script( 'jquery-ui-js', plugins_url( '/js/jquery-ui-1.12.1/jquery-ui.min.js', __FILE__ ), array('jquery'), '20151215', true );

	wp_enqueue_script( 'jquery-ui-js', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('jquery'), '20151215', true );

	wp_enqueue_script( 'jquery-ui-js-es', plugins_url( '/js/jquery-ui-1.12.1/datepicker-es.js', __FILE__ ), array('jquery'), '20151215', true );

	wp_enqueue_script( 'ans-agenda-js', plugins_url( '/js/ans-agenda.js', __FILE__ ), array('jquery'), '20151215', true );

	wp_localize_script('ans-agenda-js', 'PT_Ajax', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('ajax-post-nonce'))
	);
}

add_action( 'wp_enqueue_scripts', 'ansapp_scripts' );

function add_query_vars_filter( $vars ){
  $vars[] = "key";
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );

