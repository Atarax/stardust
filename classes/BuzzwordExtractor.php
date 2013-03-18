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
	private $weights = array();
	private $buzzwords = array();
	private $blacklist = null;

	public function addString($string, $weight = 1) {
		$this->strings[] = $string;
		$this->weights[] = $weight;
	}

	public function extract() {
		mb_internal_encoding('UTF-8');

		foreach( $this->strings as $i => $string ) {
			$weight = $this->weights[$i];

			$string = preg_replace("/[\-­]/", ' ', $string);
			$tmpWords = explode(" ", $string);

			foreach($tmpWords as $word) {
				$word = preg_replace("/[\,\.\-­\"\'?!:;“„]/", '', mb_strtolower($word));

				//$word = preg_replace("/[^A-Za-z0-9öäüß ]/", '', mb_strtolower($word));

				if( !$this->ignoreWord($word) ) {
					if( isset($this->buzzwords[$word]) ) {
						$this->buzzwords[$word] += 1 * $weight;
					}
					else {
						$this->buzzwords[$word] = 1 * $weight;
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
		if( !isset($this->blacklist) ) {
			$wordlistPath = CRON_ROOT_PATH."data/unsignificantwords";
			$this->blacklist = explode("\n", file_get_contents($wordlistPath) );
		}

		return Util::binarySearch($word, $this->blacklist) >= 0;
	}

	public function reset() {
		unset($this->strings);
		unset($this->weights);
		unset($this->buzzwords);

		$this->strings = array();
		$this->weights = array();
		$this->buzzwords = array();
	}
}
