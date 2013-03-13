<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/13/13
 * Time: 5:52 PM
 * To change this template use File | Settings | File Templates.
 */

class StardustModel {
	private static $config = array(
		"mysql_host" => "localhost",
		"mysql_user" => "root",
		"mysql_pass" => "g9H43b"
	);

	public function save() {
		$data = get_object_vars($this);
		$onupdate = array();
		$fields = array();
		$values = array();

		foreach($data as $field => $value) {
			$value = is_string($value) ? '"'.$value.'"' : $value;
			$value = empty($value) ? "NULL" : $value;
			$fields[] = $field;
			$values[] = $value;

			if( $field == "id" ) {
				continue;
			}
			$onupdate[] = $field."=".$value;
		}

		$query = "INSERT INTO contest.".lcfirst( get_class($this) )." (".implode(",", $fields).") VALUES (".implode( ",", $values ).") ";
		$query .= "ON DUPLICATE KEY UPDATE ".implode(",", $onupdate);

		file_put_contents("log/queries", date('c') . " Query: ".$query."\n", FILE_APPEND);

		mysql_connect( self::$config["mysql_host"], self::$config["mysql_user"], self::$config["mysql_pass"] );
		mysql_query( $query );

		$error = mysql_error();
		if( strlen($error) != 0 ) {
			throw new Exception($error);
		}

		mysql_close();
	}
}