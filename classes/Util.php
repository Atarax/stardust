<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/17/13
 * Time: 8:49 PM
 * To change this template use File | Settings | File Templates.
 */

class Util {
	// Algorithms
// www.scriptol.com
// Binary search by an iterative algorithm.
// Return the index to a value in an array,
// or -1 if the value can not be found.
// The contents of the table must be sorted in ascending order.
	public static function binarySearch($value,$A) {
		$starting=0;
		$ending=count($A);
		$mid=0;
		$length=0;

		while(true) {
			do {
				if($starting>$ending) {
					return -1;
				}
				$length=$ending-$starting;

				if($length===0)	{
					if( isset($A[$starting]) && $value===$A[$starting]){
						return $starting;
					}
					return -1;
				}
				$mid=$starting+intval($length/2);

				$cmp = strcmp($value,$A[$mid]);
				if($cmp < 0) {
					$ending=$mid-1;
				}
				else {
					if($cmp > 0) {
						$starting=$mid+1;
					}
					else {
						return $mid;
					}
				}
			} while(false);
		}
		return -1;
	}
}