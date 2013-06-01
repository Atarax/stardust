<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/13/13
 * Time: 5:52 PM
 * To change this template use File | Settings | File Templates.
 */

class StardustModel {
	public function save() {
		$data = get_object_vars($this);
		$onupdate = array();
		$fields = array();
		$values = array();

		$dbmanager = DatabaseManager::getInstace();
		$dbmanager->connect();

		foreach($data as $field => $value) {
			$value = is_string($value) ? '"'.mysql_real_escape_string($value).'"' : $value;
			$value = empty($value) ? "NULL" : $value;
			$fields[] = $field;
			$values[] = $value;

			if( $field == "id" ) {
				continue;
			}
			$onupdate[] = $field."=".$value;
		}

		$query = "INSERT DELAYES INTO contest.".strtolower( get_class($this) )." (".implode(",", $fields).") VALUES (".implode( ",", $values ).") ";
		$query .= "ON DUPLICATE KEY UPDATE LOW_PRIORITY ".implode(",", $onupdate);

		$dbmanager->query( $query );
		//$this->id = mysqli_insert_id();
		$dbmanager->close();
	}
}