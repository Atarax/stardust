<?php

use InvalidArgumentException;

/**
 * StringUtil class
 * class for common string functions
 */
class StringUtil {

	/**
	 * needed for mnemonic function
	 */
	const CONSONANTS = 'BCDFGHJKLMNPRSTVWXYZ';
	const VOCALS = 'AEIOU';

	/**
	 * @brief UTF-8 representation of U+200B (ZERO WIDTH SPACE)
	 * http://unicode.org/reports/tr14/#ZW
	 *
	 * does not belong in a hyphenation class, because its not hyphen ^^
	 */
	const ZERO_WIDTH_SPACE = "\xE2\x80\x8B";

	/**
	 *
	 */
	const WORD_SEPARATORS = "\r\t\n\xc2\xa0 \"',.:;!?*+=-_#~§$%&/()[]{}@\\\xc2\xab\xc2\xbb\xe2\x80\x9e\xe2\x80\x9c\xe2\x80\x9f";


	/**
	 * Converts dos to unix newlines.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function unifyNewlines($string) {
		return preg_replace("%(\r\n)|(\r)%", "\n", $string);
	}

	/**
	 * gets a string representation of a variable
	 * NOTE PHP's strval() is not safe to use on objects,
	 *      because it will try to call __toString even when __toString does not exist
	 *
	 * @param mixed $var variable to get a string representation for
	 * @return string representation of var OR '[unknown var]'
	 */
	public static function strval($var) {
		if (is_object($var)) {
			$s = get_class($var);
			if (method_exists($var, '__toString')) {
				$s = (string)$var;
			}
		} else if (is_resource($var)) {
			$s = strval($var) . ': ' . get_resource_type($var);
		} else {
			$s = strval($var);
		}
		return $s;
	}

	/**
	 * is this email valid?
	 * @param string
	 * @return boolean
	 */
	public static function isValidEmail($string) {
		return preg_match('/^[^@]+@([a-z0-9A-Z_-]{1,63}\.)+[a-zA-Z]{2,6}$/', $string);
	}

	/**
	 * length of string
	 * @param $string
	 * @return int
	 */
	public static function length($string) {
		return mb_strlen($string);
	}

	/**
	 * is this hostname valid?
	 * @param string
	 * @return boolean
	 */
	public static function isValidHostname($string) {
		$string = str_replace(array('http://', 'www.'), '', $string);

		// the converter is clever enough to check if conversion is needed :)
		$encoder = new \DomainNameConverter();
		$string = $encoder->encode($string);

		return preg_match('/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/', $string);
	}

	/**
	 * at least more strict than isValidHostname
	 * @param string
	 * @return boolean
	 */
	public static function isValidHostnameStrict($string) {
		return preg_match('/^(http:\/\/|https:\/\/|)[a-z0-9_\-äöü\.\/]+\.[a-z0-9]{2,10}$/si', $string);
	}

	/**
	 * @param $string
	 * @return int
	 */
	public static function isKindOfUrl($string) {
		return preg_match('/^(http:\/\/|https:\/\/|)[a-z0-9_\-äöü\.\/]+\.[a-z0-9]{2,10}([\/|\?].*)?$/si', $string);
	}

	/**
	 * is this phone number valid?
	 * @param $string
	 * @return boolean
	 */
	public static function isValidPhoneNumber($string) {
		return preg_match('/^[\+]?([0-9]?)[\(|\s|\-|\.]?([0-9]{3})[\)|\s|\-|\.]*([0-9]{3})[\s|\-|\.]*([0-9]{4})$/', $string);
	}

	/**
	 * obsolete
	 * @param string
	 * @return boolean
	 */
	public static function stringIsEmail($string) {
		return self::isValidEmail($string);
	}

	/**
	 * returns a beautiful name from hostname
	 * used for filenames of images
	 *
	 * @param string
	 * @return string
	 */
	public static function fromHostname($hostname) {
		if (empty($hostname)) return '';
		if (!preg_match('/^http/', $hostname)) $hostname = 'http://' . $hostname;
		$key = parse_url($hostname, PHP_URL_HOST);
		return $key;
	}

	/**
	 * @brief evaluate contents of a php file
	 * @param string $src contents of php src file
	 * @return string output of php file
	 */
	public static function getEvaled($src) {
		$src = trim($src);
		if (!$src) {
			return '';
		}

		$first = "?>";
		$last = "";
		if (substr($src, strlen($src) - 2) == "?>") {
			$last = "<?";
		}
		ob_start();
		eval($first . $src . $last);
		return ob_get_clean();
	}

	/**
	 * returns a random string
	 *
	 * @param $length        integer
	 * @param $mnemonic        boolean                        use a string which is better to remember
	 * @return                 string
	 */
	public static function getRandomString($length = 8, $mnemonic = false) {
		$password = "";

		if ($mnemonic) {
			for ($i = 1; $i <= $length; $i++) {
				if ($i % 2) $password .= substr(self::CONSONANTS, mt_rand(0, 19), 1);
				else $password .= substr(self::VOCALS, mt_rand(0, 4), 1);
			}
		} else {
			while (strlen($password) < $length) {
				$array[0] = chr(mt_rand(48, 57)); // zero to nine
				$array[1] = chr(mt_rand(65, 90)); // capitals
				$array[2] = chr(mt_rand(97, 122)); // non-capitals
				$password .= $array[mt_rand(0, (count($array) - 1))];
			}
		}

		return $password;
	}

	/**
	 * makes sure a string is utf8 encoded
	 * @param $str
	 * @return string
	 */
	public static function utf8_ensure($str) {
		return mb_check_encoding($str, 'UTF-8') ? $str : utf8_encode($str);
	}

	/**
	 * @note this function now is only a wrapper for mb_check_encoding (which was broken before php 5.2.-something)
	 * @throws \InvalidArgumentException
	 * @param $str string to test for utf-8
	 * @return bool (boolean) - if string is valid utf-8
	 */
	public static function check_utf8($str) {
		if (!is_string($str)) {
			throw new InvalidArgumentException('string argument required!');
		}
		return mb_check_encoding($str, 'UTF-8');
	}

	/**
	 * determine if a string contains double-encoded utf-8 sequences
	 * @param $str
	 * @return bool
	 */
	public static function hasUtf8DoubleEncoded($str) {
		if (self::check_utf8($str)) {
			$tmp = utf8_decode($str);
			$len = strlen($tmp);
			for ($i = 0; $i < $len; $i++) {
				$c = ord($tmp[$i]);
				if ($c > 128) {
					if (($c > 247)) continue;
					elseif ($c > 239) $bytes = 4;
					elseif ($c > 223) $bytes = 3;
					elseif ($c > 191) $bytes = 2;
					else continue;
					if (($i + $bytes) > $len) return false;
					while ($bytes > 1) {
						$i++;
						$b = ord($tmp[$i]);
						$bytes--;
						if ($b < 128 || $b > 191) continue 2;
					}
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * replaces all linebreaks to its characers (\n and \r) - calls the toString method on object and also escape method
	 * @param object $content
	 * @internal param object $content or string
	 * @return string         escaped oneliner
	 */
	public static function toInnerHTML($content) {
		return str_replace(array("\n", "\r"), array('\n', '\r'), addslashes((string)$content));
	}

	/**
	 * Converts html special characters.
	 *
	 * @param         string                 $string
	 * @return         string                 $string
	 */
	public static function encodeHTML($string) {
		if (is_object($string))
			$string = $string->__toString();

		return @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Decodes html entities.
	 *
	 * @param         string                 $string
	 * @return         string                 $string
	 */
	public static function decodeHTML($string) {
		if (is_object($string))
			$string = $string->__toString();

		$string = str_ireplace('&nbsp;', ' ', $string); // convert non-breaking spaces to ascii 32; not ascii 160
		return @html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Formats an integer.
	 *
	 * @param        integer                $integer
	 * @return        string
	 */
	public static function formatInteger($integer) {
		$integer = self::addThousandsSeparator($integer);

		return $integer;
	}

	/**
	 * Formats a double.
	 *
	 * @param float $double
	 * @return string
	 */
	public static function formatDouble($double) {
		// consider as integer, if no decimal places found
		if (preg_match('~^(-?\d+)(?:\.(?:0*|00[0-4]\d*))?$~', $double, $match)) {
			return self::formatInteger($match[1]);
		}

		// round
		$double = round($double, 2);

		// remove last 0
		if (substr($double, -1) == '0') $double = substr($double, 0, -1);

		// replace decimal point
		$decimalPoint = '.';
		$double = str_replace('.', $decimalPoint, $double);

		// add thousands separator
		$double = self::addThousandsSeparator($double);

		return $double;
	}

	/**
	 * Adds thousands separators to a given number.
	 *
	 * @param        mixed                $number
	 * @return        string
	 */
	public static function addThousandsSeparator($number) {
		if ($number >= 1000 || $number <= -1000) {
			$thousandsSeparator = '';
			$number = preg_replace('~(?<=\d)(?=(\d{3})+(?!\d))~', $thousandsSeparator, $number);
		}

		return $number;
	}

	/**
	 * converts special characters to html entities.
	 * not more than a wrapper for htmlspecialchars
	 *
	 * @param         string                 $string
	 * @return         string                 $string
	 */
	public static function noHTML($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Tests if a text starts with an given string.
	 *
	 * @param         string                 $haystack
	 * @param        string                $needle
	 * @param        bool                $asCaseSensitive - optional and default on true, false for case insensitive
	 * @return    bool
	 */
	public static function startsWith($haystack, $needle, $asCaseSensitive = true) {
		if ($asCaseSensitive) {
			return 0 === strpos($haystack, $needle);
		} else {
			return 0 === stripos($haystack, $needle);
		}
	}


	/**
	 * does the haystack end with the needle?
	 *
	 * @param         string                 $haystack
	 * @param        string                $needle
	 * @return        boolean
	 */
	public static function endsWith($haystack, $needle) {
		return ($x = mb_strrpos($haystack, $needle)) !== false && $x == mb_strlen($haystack) - mb_strlen($needle);
	}

	/**
	 * Removes a GET parameter from a given url.
	 * @param string $url the url
	 * @param string $parameterName the parameter to remove
	 * @return string
	 */
	public static function removeParameterFromUrl($url, $parameterName) {
		// remove paramter from url if it already exists
		$url = preg_replace('/[&]*' . $parameterName . '=[^&]*/i', "", $url);

		// clean up url
		return str_replace("?&", "?", $url);
	}

	/**
	 * FIXME missing URL parameter encoding! also, consider using http_build_query and parse_str -- ms
	 *
	 *
	 * Adds a GET parameter to a given url. If a parameter already exists,
	 * the current value will be replaced with the new one, by using
	 * removeParameterFromUrl.
	 *
	 * @param string $url the url
	 * @param string $parameterName the parameter name to add
	 * @param string $parameterValue the parameter value to add
	 * @return string
	 *
	 */
	public static function addParameterToUrl($url, $parameterName, $parameterValue) {
		// remove existing parameter
		$url = StringUtil::removeParameterFromUrl($url, $parameterName);

		// add new parameter
		if (strpos($url, "?") !== false) {
			$url .= "&" . $parameterName . "=" . $parameterValue;
		} else {
			$url .= "?" . $parameterName . "=" . $parameterValue;
		}

		// clean up url
		return str_replace("?&", "?", $url);

	}

	/**
	 * alias to php strtolower() function.
	 * @param $string
	 * @return string
	 */
	public static function toLowerCase($string) {
		return mb_strtolower($string);
	}

	/**
	 * alias to php strtoupper() function.
	 * @param $string
	 * @return string
	 */
	public static function toUpperCase($string) {
		return mb_strtoupper($string);
	}

	/**
	 * remove all consecutive SPACE characters
	 * @param string $text
	 * @return string
	 */
	public static function makeSingleWhitespace($text) {
		return preg_replace('/( +)/', ' ', $text);
	}

	/*
	 * parses a text
	 * @param String $text - text to parse
	 * @return 'clean String'
	 */
	public static function fromJavadoc($text) {
		$newtext = array();
		foreach (explode("\n", $text) as $row) {
			preg_match('/.+\* (.+)/', $row, $match);
			if (!isset($match[1]) || empty($match[1]) || preg_match('/\W?\*\W*@/', $row)) {
				continue;
			}
			$newtext[] = $match[1];
		}
		return implode("<br/>", $newtext);
	}

	/**
	 * @obsolete use replace instead
	 * @param $search
	 * @param $replace
	 * @param $subject
	 * @param string $encoding
	 * @return string
	 */
	public static function mb_str_replace($search, $replace, $subject, $encoding = 'auto') {
		return self::replace($search, $replace, $subject, $encoding);
	}

	/**
	 * multibyte safe replace function
	 *
	 * @param string|array $search
	 * @param string|array $replace
	 * @param string|array $subject
	 * @param string $encoding
	 * @return string
	 */
	public static function replace($search, $replace, $subject, $encoding = 'auto') {
		if (!is_array($search)) {
			$search = array($search);
		}
		if (!is_array($replace)) {
			$replace = array($replace);
		}
		if (strtolower($encoding) === 'auto') {
			$encoding = mb_internal_encoding();
		}
		if (is_array($subject)) {
			$result = array();
			foreach ($subject as $key => $val) {
				$result[$key] = self::replace($search, $replace, $val, $encoding);
			}
			return $result;
		}

		$currentpos = 0;
		while (true) {
			$index = -1;
			$minpos = -1;
			foreach ($search as $key => $find) {
				if ($find == '') {
					continue;
				}
				$findpos = mb_strpos($subject, $find, $currentpos, $encoding);
				if ($findpos !== false) {
					if ($minpos < 0 || $findpos < $minpos) {
						$minpos = $findpos;
						$index = $key;
					}
				}
			}
			if ($minpos < 0) {
				break;
			}

			$r = array_key_exists($index, $replace) ? $replace[$index] : '';
			$subject = sprintf('%s%s%s',
				mb_substr($subject, 0, $minpos, $encoding),
				$r,
				mb_substr(
					$subject,
					$minpos + mb_strlen($search[$index], $encoding),
					mb_strlen($subject, $encoding),
					$encoding
				)
			);
			$currentpos = $minpos + mb_strlen($r, $encoding);
		}
		return $subject;
	}

	/**
	 *
	 * @see http://www.php.net/manual/en/function.ord.php#78032
	 * @param $c
	 * @param int $index
	 * @param null $bytes
	 * @param int $len
	 * @return bool|int
	 */
	public static function ordUTF8($c, $index = 0, &$bytes = null, $len = 0) {
		if ($len == 0) {
			$len = strlen($c);
		}
		$bytes = 0;
		if ($index >= $len)
			return false;
		$h = ord($c{$index});
		if ($h <= 0x7F) {
			$bytes = 1;
			return $h;
		} else if ($h < 0xC2)
			return false;
		else if ($h <= 0xDF && $index < $len - 1) {
			$bytes = 2;
			return ($h & 0x1F) << 6 | (ord($c{$index + 1}) & 0x3F);
		} else if ($h <= 0xEF && $index < $len - 2) {
			$bytes = 3;
			return ($h & 0x0F) << 12 | (ord($c{$index + 1}) & 0x3F) << 6 | (ord($c{$index + 2}) & 0x3F);
		} else if ($h <= 0xF4 && $index < $len - 3) {
			$bytes = 4;
			return ($h & 0x0F) << 18 | (ord($c{$index + 1}) & 0x3F) << 12 | (ord($c{$index + 2}) & 0x3F) << 6 | (ord($c{$index + 3}) & 0x3F);
		} else
			return false;
	}

	public static function getUtfHash($str) {
		$r = 0;
		$len = strlen($str);
		$bytes = null;
		for ($i = 0; $i < $len; $i++) {
			$x = self::ordUTF8($str, $i, $bytes, $len);
			$r += $x;
		}
		return $r;
	}

	/**
	 * Checks if a substring is a separate word.
	 * @param string $text contains the substring
	 * @param string $word the substring
	 * @param int $pos the substring's position (NOTE: character offset, <em>not</em> byte offset)
	 * @return boolean
	 */
	public static function isSeparateWord(&$text, $word, $pos) {
		//
		// unicode. utf-8........... literal character
		//
		// 00a0 ... \xc2\xa0 ....... nbsp
		// 00ab ... \xc2\xab ....... «
		// 00bb ... \xc2\xbb ....... »
		// 201e ... \xe2\x80\x9e ... „
		// 201c ... \xe2\x80\x9c ... “
		// 201f ... \xe2\x80\x9f ... ‟
		//
		// NOTE a similar list of word separators is found in plugin/scripts/intext.js
		// plz synchronize changes
		$separators = self::WORD_SEPARATORS;
		$posAfter = $pos + mb_strlen($word);
		return (mb_strpos($separators, $pos ? mb_substr($text, $pos - 1, 1) : ' ') !== false) &&
			(mb_strpos($separators, mb_strlen($text) > $posAfter ? mb_substr($text, $posAfter, 1) : ' ') !== false);
	}


	/**
	 * Strips HTML tags from a string.
	 *
	 * @param       string          $string
	 * @return      string
	 */
	public static function stripHTML($string) {
		return preg_replace('~</?[a-z]+[1-6]?
                        (?:\s*[a-z]+\s*=\s*(?:
                        "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^\s>]
                        ))*\s*/?>~ix', '', $string);
	}

	/**
	 * same as substr_replace but with multibyte support
	 * @param $string
	 * @param $replacement
	 * @param $start
	 * @param $length
	 * @param $encoding
	 * @return string
	 */
	public static function substr_replace($string, $replacement, $start, $length = null, $encoding = null) {
		if (extension_loaded('mbstring') === true) {
			$string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);

			if ($start < 0) {
				$start = max(0, $string_length + $start);
			} else if ($start > $string_length) {
				$start = $string_length;
			}

			if ($length < 0) {
				$length = max(0, $string_length - $start + $length);
			} else if ((is_null($length) === true) || ($length > $string_length)) {
				$length = $string_length;
			}

			if (($start + $length) > $string_length) {
				$length = $string_length - $start;
			}

			if (is_null($encoding) === true) {
				return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
			}

			return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
		}

		return (is_null($length) === true) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
	}

	/**
	 * @brief return mime type of a string
	 * @param string $buffer
	 * @return string
	 */
	public static function getContentType($buffer) {
		return finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $buffer);
	}

	/**
	 * small wrapper for json_encode(), checks whether a function named __toJSON() is callable on the passed argument
	 * and in that case returns its return value. otherwise json_encode() is called with the passed arguments and
	 * that value is returned with all line breaks ("\r", "\n") stripped.
	 * @param mixed $elem the thing to be encoded
	 * @return string the argument encoded into JSON
	 */
	public static function plista_json_encode($elem) {
		if (is_object($elem)) {
			if (is_callable(array($elem, '__toJSON'))) {
				return $elem->__toJSON();
			}

			// implement recursion into objects here?
		}

		return json_encode($elem);
	}


	/**
	 * @brief parses INI file.
	 *
	 * PHP's native parse_ini_string function is a total asshole:
	 * It validates keys/values, complains about # as comment start et al.
	 * This function, in contrast, tries to be as forgiving as possible:
	 * @li accepts comments starting with "#" and ";"
	 * @li key value separator is the first "="
	 * @li no restrictions on keys
	 * @li knows sections
	 *
	 * @param string $s contents of the ini file
	 * @param int $opts optional flags:
	 *                  1... process subsections. allows array-like syntax to nest sections
	 *                       example:
	 *                          key[]=value
	 *                          key[]=value2
	 *                          key[moo]=value3
	 *                       becomes
	 *                         array(
	 *                           'key' => array('value', 'value2', 'moo' => 'value3')
	 *                         )
	 *                 2... allow that a key is repeated. then, the structured is changed.
	 *                        array(key => value) becomes array(array(key, value))
	 * @return array  example:
	 *                    array('section' => array('key' => 'value', 'key2' => 'value2'))
	 */
	public static function parse_ini_string($s, $opts = 1) {
		$ALLOWKEYREPETITIONS = $opts & 2;
		$ALLOWSUBSECTIONS = $opts & 1;

		$result = array();
		$lines = explode("\n", $s);
		$currentSection = '';
		foreach ($lines as $line) {

			// respect all comments, anywhere!
			// also, respect my authority!
			$line = trim($line);
			$line = explode(';', $line, 2);
			$line = explode('#', $line[0], 2);
			$line = $line[0];

			if (preg_match('/^\[([^\]]+)\]\s*$/', $line, $match)) {
				$currentSection = $match[1];
			} else if ($line) {

				$linebits = explode('=', $line, 2);

				// do we have subsections?
				if ($ALLOWSUBSECTIONS) {
					// if yes, the key has to end with "]", and also contain a "["
					// example: "section[subsection][subsubsection]"
					$key = $linebits[0];
					if (self::endsWith($key, "]") && (strpos($key, "[") !== false)) {
						//now explode the whole thing

						$hierarchy = explode("[", $key, 2);
						// example: now we have array('section', 'subsection][subsubsection]')

						// pop off the closing bracket from the end
						$hierarchy[1] = substr($hierarchy[1], 0, -1);
						$hierarchy = array_merge(array($hierarchy[0]), array_map(function ($e) {
							return $e ? $e : 0;
						}, explode('][', $hierarchy[1])));


					} else {
						$hierarchy = array($linebits[0]);
					}
				} else {
					$hierarchy = array($linebits[0]);
				}

				if ($currentSection) {
					if (!isset($result[$currentSection])) {
						$result[$currentSection] = array();
					}
					$arrayToAppend =& $result[$currentSection];
				} else {
					$arrayToAppend = &$result;
				}

				// work ourselves deep into the hierarchy
				while (count($hierarchy) > 1) {
					$nextLevel = array_shift($hierarchy);
					if (!isset($arrayToAppend[$nextLevel])) {
						$arrayToAppend[$nextLevel] = array();
					}
					$arrayToAppend =& $arrayToAppend[$nextLevel];
				}

				$keyToSet = array_shift($hierarchy);
				$valToSet = $linebits[1];

				if ($ALLOWKEYREPETITIONS) {
					$arrayToAppend[] = array($keyToSet, $valToSet);
				} else {
					if ($keyToSet !== 0) {
						$arrayToAppend[$keyToSet] = $valToSet;
					} else {
						$arrayToAppend[] = $valToSet;
					}
				}

			}
		}
		return $result;
	}


	/**
	 * strip parentheses (or any other character pair *equally from both* sides of a string
	 *
	 * examples:
	 * @code
	 * StringUtil::stripParens('((4 && 2))') === '4 && 2';
	 * StringUtil::stripParens('[(0-9)]', array('(', '['), array(')', ']')) === '0-9';
	 * @endcode
	 *
	 * @throws \InvalidArgumentException
	 * @param string $subject
	 * @param array $open characters that may open the statement
	 * @param array $close characters that may close the statement
	 * @return string
	 */
	public static function stripParens($subject, $open = array('('), $close = array(')')) {
		if (count($open) !== count($close)) {
			throw new InvalidArgumentException('need balanced delimiters');
		}
		$previousLengthWithinLoop = -1;
		while (($lengthWithinLoop = strlen($subject)) !== $previousLengthWithinLoop) {

			foreach ($open as $key => $oc) {

				$cc = $close[$key];

				if (self::startsWith($subject, $oc) && self::endsWith($subject, $cc)) {
					$subject = substr($subject, 1, strlen($subject) - 2);
				}
			}
			$previousLengthWithinLoop = $lengthWithinLoop;
		}
		return $subject;
	}

#
	/**
	 * special helper function for adcloud.net importer
	 * they use a slightly modified Base64 encoding where they replace / with * and = with @
	 * @param $i string input
	 * @return string base64 special-encoded
	 */
	public static function adcloud_base64_encode($i) {
		return rtrim(strtr(base64_encode($i), '/=', '*@'), '=');
	}

}
