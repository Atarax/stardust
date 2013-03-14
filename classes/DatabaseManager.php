<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

class DatabaseManager {
	private $connected = false;

	private static $config = array(
		"mysql_host" => "localhost",
		"mysql_user" => "root",
		"mysql_pass" => "yfmaster00pw"
	);

	public function connect() {
		mysql_connect( self::$config["mysql_host"], self::$config["mysql_user"], self::$config["mysql_pass"] );

		$error = mysql_error();
		if( strlen($error) != 0 ) {
			throw new Exception($error);
		}

		mysql_set_charset( "utf8" );
		$this->connected = true;
	}

	public function query($query) {
		if( !$this->connected ) {
			$this->connect();
			$instantQuery = true;
		}

		$res = mysql_query( $query );

		$error = mysql_error();
		if( strlen($error) != 0 ) {
			throw new Exception($error);
		}

		if( isset($instantQuery) ) {
			$this->close();
		}

		if( $res !== true ) {
			$data = array();

			while( $row = mysql_fetch_array($res, MYSQL_ASSOC) ) {
				$data[] = $row;
			}
			return $data;
		}
		return $res;
	}

	public function close() {
		mysql_close();
		$this->connected = true;
	}
}