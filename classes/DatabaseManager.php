<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

class DatabaseManager {
	private static $instance = null;
	private $connection = null;

	public static function getInstace() {
		if( !isset(self::$instance) ) {
			self::$instance = new DatabaseManager();
		}
		return self::$instance;
	}
	public function connect() {
		if( isset($this->connection) ) {
			return;
		}
		$this->connection = mysql_connect( MYSQL_HOST, MYSQL_USER, MYSQL_PASS );

		$error = mysql_error();
		if( strlen($error) != 0 ) {
			throw new Exception($error);
		}

		mysql_set_charset( "utf8" );
	}

	public function query($query) {
		if( !isset($this->connection) ) {
			$this->connect();
			$instantQuery = true;
		}

		$res = mysql_query( $query, $this->connection );

		$error = mysql_error();
		if( strlen($error) != 0 ) {
			throw new Exception($error);
		}

		if( $res !== true ) {
			$data = array();

			while( $row = mysql_fetch_array($res, MYSQL_ASSOC) ) {
				$data[] = $row;
			}
			return $data;
		}

		if( isset($instantQuery) ) {
			$this->close();
		}

		return $res;
	}

	public function close() {
		mysql_close($this->connection);
		$this->connection = null;
	}
}