<?php


class ansAgendaMain {

	function install() {
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
       
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix.'ans_agenda';

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    	$sql = "CREATE TABLE ".$table_name." (
                id int(11) NOT NULL AUTO_INCREMENT,
                payment_id VARCHAR(250) DEFAULT '',
                pay_mode VARCHAR(10) DEFAULT '' NOT NULL,
                creation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                name VARCHAR(250) DEFAULT '' NOT NULL,
                country VARCHAR(100) DEFAULT '' NOT NULL,
                email VARCHAR(250) DEFAULT '',
                phone VARCHAR(45) DEFAULT '',
                date DATE NOT NULL,
                hour TIME NOT NULL,
                customer_hour TIME NOT NULL,
                customer_timezone VARCHAR(100) DEFAULT '',
                amount DOUBLE NOT NULL,
                paid TINYINT(1) NULL,
                UNIQUE KEY id (id)
            )".$charset_collate.";";

      dbDelta( $sql );
    }
	}

  public static function save_data() {
    $nonce = $_POST['nonce'];
    if (!wp_verify_nonce($nonce, 'ajax-post-nonce')) {
      die (' ');
    }
    echo "datos saved...";
    die();
  }

}