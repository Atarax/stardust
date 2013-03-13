<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/13/13
 * Time: 5:52 PM
 * To change this template use File | Settings | File Templates.
 */

class StardustModel {
	private $config = array(
		"mysql_host" => "localhost",
		"mysql_user" => "root",
		"mysql_pass" => "g9H43b"
	);

	public function save() {
		$data = get_object_vars($this);
		$values = array();

		$query = "INSERT INTO contest.".lcfirst( get_class($this) )." (".implode(",", array_keys($data)).") VALUES (".implode( ",", $this->enQuoteData( array_values($data) ) ).") ";

		foreach($data as $field => $value) {
			if( $field == "id" ) {
				continue;
			}
			$values[] = $field."=".( is_string($value) ? '"'.$value.'"' : $value );
		}

		$query .= "ON DUPLICATE KEY UPDATE ".implode(",", $values);

		mysql_connect( self::$config["mysql_host"], self::$config["mysql_user"], self::$config["mysql_pass"] );
		mysql_query( $query );

		$error = mysql_error();
		if( strlen($error) != 0 ) {
			throw new Exception($error);
		}

		mysql_close();
	}

	private function enQuoteData($data) {
		foreach($data as $key => $val) {
			if( is_string($val) )  {
				$data[$key] = '"'.$val.'"';
			}
		}

		return $data;
	}
}