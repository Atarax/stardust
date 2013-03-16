<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 10:36 AM
 * To change this template use File | Settings | File Templates.
 */
class RssReader {
	public function read($url) {
		// Feed einlesen
		if( !$xml = simplexml_load_file($url) ) {
			die('Fehler beim Einlesen der XML Datei!');
		}

		// Ausgabe Array
		$out = array();

		// auszulesende Datensaetze

		// Items vorhanden?
		if( !isset($xml->channel[0]->item) ) {
			die('Keine Items vorhanden!');
		}
		else {
			$i = count($xml->channel[0]->item);
		}

		// Items holen
		foreach($xml->channel[0]->item as $item) {
			if( $i-- == 0 ) {
				break;
			}

		$out[] = array(
			'title'        => (string) $item->title,
			'description'  => (string) $item->description,
			'link'         => (string) $item->guid,
			'date'         => date('d.m.Y H:i', strtotime((string) $item->pubDate))
		);
		}

		// Eintraege ausgeben
		foreach ($out as $value) {
				echo $value['title'].$value['description'].$value['link']."<br>";
		}
	}

}
