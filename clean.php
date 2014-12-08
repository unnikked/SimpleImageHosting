<?php 

$f3=require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

$f3->config('config.ini');

if(!$f3->exists("days")) $f3->set("days", 7);

$f3->set('DB', new DB\SQL('mysql:host='.$f3->get('mysql_host').';port=3306;dbname='.$f3->get('mysql_name').'', $f3->get('mysql_user'), $f3->get('mysql_pass')));

$entries = $f3->get('DB')->exec("SELECT path FROM uploads WHERE timestamp < NOW()- :days;", array(":days" => $f3->get("days") * 24 * 60 * 60));

if(!empty($entries)) {
	foreach ($entries as $entry) {
		unlink($entry["path"]);
		echo "Deleted " . $entry["path"] . " ";
	}
}

$f3->get('DB')->exec("DELETE FROM uploads WHERE timestamp < NOW() - :days;", array(":days" => $f3->get("days") * 24 * 60 * 60));

echo "Executed!";