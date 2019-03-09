<?php

class ansAgendaDBA {

	static $primary_key = 'id';

	private static function _table() {
		global $wpdb;
		$tablename = "ans_agenda";
		return $wpdb->prefix . $tablename;
	}

	private static function _fetch_sql( $field, $value ) {
		global $wpdb;
		$sql = sprintf( 'SELECT * FROM %s WHERE %s = %%s', self::_table(), $field, static::$primary_key );
		return $wpdb->prepare( $sql, $value );
	}

	static function valid_check( $data ) {
		global $wpdb;

		$sql_where       = '';
		$sql_where_count = count( $data );
		$i               = 1;
		foreach ( $data as $key => $row ) {
			if ( $i < $sql_where_count ) {
				$sql_where .= "`$key` = '$row' and ";
			} else {
				$sql_where .= "`$key` = '$row'";
			}
			$i++;
		}
		$sql     = 'SELECT * FROM ' . self::_table() . " WHERE $sql_where";
		$results = $wpdb->get_results( $sql );
		if ( count( $results ) != 0 ) {
			return false;
		} else {
			return true;
		}
	}

	static function get( $field, $value ) {
		global $wpdb;
		return $wpdb->get_row( self::_fetch_sql( $field, $value ) );
	}

	static function insert( $data ) {
		global $wpdb;
		return $wpdb->insert( self::_table(), $data );
	}

	static function update( $data, $where ) {
		global $wpdb;
		return $wpdb->update( self::_table(), $data, $where );
	}

	static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );
		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}

	static function fetch( $value ) {
		global $wpdb;
		$value = intval( $value );
		//$sql   = 'SELECT * FROM ' . self::_table() . " WHERE `site_id` = '$value' order by `created_at` DESC";
		$sql   = 'SELECT * FROM ' . self::_table() . " order by `date` DESC";
		return $wpdb->get_results( $sql );
	}

	static function getFreeHours( $date ) {
		global $wpdb;
		//$value = intval( $value );
		$sql   = 'SELECT * FROM ' . self::_table() . " WHERE `date` = '$date' order by `hour` ASC";
		return $wpdb->get_results( $sql );
	}

	static function Remove() {
		global $wpdb;
		//$value = intval( $value );
		$sql   = 'SELECT * FROM ' . self::_table() . " WHERE `paid` = '0' order by `hour` ASC";
		return $wpdb->get_results( $sql );
	}
}