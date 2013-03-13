<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/13/13
 * Time: 2:42 PM
 * To change this template use File | Settings | File Templates.
 */

$config["mysql_host"] = "localhost";
$config["mysql_user"] = "root";
$config["mysql_pass"] = "g9H43b";


class Item {
	public $id;
	public $domain;
	public $recommendable;

	public function save() {
		/**
		 * @var $config defined in config.php
		 */
		mysql_connect( $config["mysql_host"], $config["mysql_user"], $config["mysql_pass"] );
		mysql_query( "INSERT INTO contest.item (id, domain, recommendable) VALUES (".$this->id.",". $this->domain.",". $this->recommendable.") ON DUPLICATE KEY UPDATE" );
		mysql_close();
	}

	public function find() {

	}
}