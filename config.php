<?php
// headline-link
// api.zeit.de/content?q=department:wirtschaft OR department :politik OR department:digital&limit=100&sort=release_date desc&fields=title&api_key=a374ba6ae49faeb3af267874fb185392914670071e2b14b1a067
mb_internal_encoding('UTF-8');
setlocale(LC_ALL, "de_DE.utf8");

// this function tries to autoload classes whose definition is unknown to the interpreter at runtime
function autoload_plista_contest($className) {
	if (is_readable(dirname(__FILE__) . '/classes/' . $className . '.php')) {
		require_once dirname(__FILE__) . '/classes/' . $className . '.php';
	}
}
spl_autoload_register('autoload_plista_contest');

// this function is a simple wrapper around json_encode and tries to call __toJSON() on any object it gets passed first
function plista_json_encode($elem) {
	if (is_object($elem)) {
		if (is_callable(array($elem, '__toJSON'))) {
			return $elem->__toJSON();
		}
	}

	return json_encode($elem);
}

require_once("config_local.php");
