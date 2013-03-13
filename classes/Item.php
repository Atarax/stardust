<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/13/13
 * Time: 2:42 PM
 * To change this template use File | Settings | File Templates.
 */

class Item {
	private $config = array(
		"mysql_host" => "localhost",
		"mysql_user" => "root",
		"mysql_pass" => "yfmaster00pw"
	);

	public $id;
	public $domain;
	public $recommendable;

	public function save() {
		/**
		 * @var $config defined in config.php
		 */
		if(!isset($this->id))
		mysql_connect( $this->config["mysql_host"], $this->config["mysql_user"], $this->config["mysql_pass"] );
		mysql_query( "
				INSERT INTO contest.item (id, domain, recommendable)
				VALUES (".$this->id.",". $this->domain.",". $this->recommendable.")
				ON DUPLICATE KEY UPDATE
					domain=".$this->domain.",
					recommendable=".$this->recommendable
		);
		mysql_close();
	}

	public function find() {

	}
}