<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 10:46 AM
 * To change this template use File | Settings | File Templates.
 */
class BuzzwordExtractor {
	private $strings = array();
	private $buzzwords = array();

	public function addString($string) {
		$this->strings[] = $string;
	}

	public function extract() {
		foreach( $this->strings as $string ) {
			$tmpWords = explode(" ", $string);

			foreach($tmpWords as $word) {
				$word = preg_replace("/[\,\.\-\"\'?!]/", '', mb_strtolower($word));

				//$word = preg_replace("/[^A-Za-z0-9öäüß ]/", '', mb_strtolower($word));

				if( !$this->ignoreWord($word) ) {
					if( isset($this->buzzwords[$word]) ) {
						$this->buzzwords[$word]++;
					}
					else {
						$this->buzzwords[$word] = 1;
					}
				}
			}
		}

		return $this->buzzwords;
	}

	private function ignoreWord($word) {
		if( strlen($word) < 2 ) {
			return true;
		}

		$wordlistPath = CRON_ROOT_PATH."data/unsignificantwords";
		$blacklist = explode("\n", file_get_contents($wordlistPath) );

		// TODO: Add binary search here
		return in_array($word, $blacklist);
	}
}
