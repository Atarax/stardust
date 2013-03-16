<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 1:44 PM
 * To change this template use File | Settings | File Templates.
 */
class MessageMirror {
	private $mirror;
	private $mirrorFile = "data/mirror";

	public function __construct() {
		if( !file_exists($this->mirrorFile) ) {
			$this->mirror = null;
		}
		else {
			$this->mirror = file_get_contents($this->mirrorFile);
		}
	}

	public function mirror(ContestMessage $message) {
		if( !empty($this->mirror) ) {
			$message->postTo($this->mirror);
		}
	}

	public function setMirror($mirror = null) {
		if( empty($mirror) ) {
			unlink($this->mirrorFile);
		}
		else {
			file_put_contents($this->mirrorFile, $mirror);
		}
	}

	public function getMirror() {
		return $this->mirror;
	}
}
