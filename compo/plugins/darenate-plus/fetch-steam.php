<?php

// http://upskill.co.in/content/how-convert-simplexml-object-array-php
function xml2array($xml) {
	$arr = array();

	foreach ($xml->children() as $r) {
		$t = array();

		if (count($r->children()) == 0) {
			$arr[$r->getName()] = strval($r);
		}
		else {
			$arr[$r->getName()][] = xml2array($r);
		} 
	}
	return $arr;
}


echo "Steam Group (XML)\n";
{
	$url = "http://steamcommunity.com/groups/ludum/memberslistxml/?xml=1";
	$xml = simplexml_load_file($url);
	$arr = xml2array($xml);
	
	//print_r($arr['groupDetails'][0]);
	
	$group = &$arr['groupDetails'][0];
	
	echo "Members: " . $group['memberCount'] . 
		" [In Game: " . $group['membersInGame'] . "] [Online: " . $group['membersOnline'] . "]\n";
}

echo "\n";

// http://simplehtmldom.sourceforge.net/manual.htm
require "simple_html_dom.php";

echo "Curator (HTML)\n";
{
	$html = file_get_html( "http://store.steampowered.com/curator/537829/" );
	
	echo "Followers: " . $html->find('.num_followers', 0)->plaintext . "\n";
	
	
}

?>