<?php

$f3=require('lib/base.php');

if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

$f3->config('config.ini');

$f3->set('DB', new DB\SQL('mysql:host='.$f3->get('mysql_host').';port=3306;dbname='.$f3->get('mysql_name').'', $f3->get('mysql_user'), $f3->get('mysql_pass')));

if(!$f3->exists("max_file_size")) $f3->set("max_file_size", 8);
$f3->set("ui_error_messages", false);

$f3->route('GET /', function($f3) {
	echo Template::instance()->render('home.html');
});

$f3->route('POST /', function($f3) {
	$web = Web::instance();
	$overwrite = false; // set to true, to overwrite an existing file; Default: false
	$files = $web->receive(function($file, $formFieldName) {
	        if ($file['type'] != 'image/png' && $file['type'] != 'image/jpeg' && $file['type'] != 'image/gif') return false;
	        // maybe you want to check the file size
	        if($file['size'] > (F3::instance()->get("max_file_size") * 1024 * 1024)) // if bigger than max_file_size MB
	            return false; // this file is not valid, return false will skip moving it
	        return true; // allows the file to be moved from php tmp dir to your defined upload dir
	    },
	    $overwrite,
	    function($fileBaseName, $formFieldName) {
	    	$mime = Web::instance()->mime($fileBaseName);
	    	if($mime == 'image/png') return base_convert(time(), 10, 16) . '.png';
	    	if($mime == 'image/jpeg') return base_convert(time(), 10, 16) . '.jpg';
	    	if($mime == 'image/gif') return base_convert(time(), 10, 16) . '.gif';
	    	return $fileBaseName;
	    }
	);

	foreach ($files as $key) {
		if($key) {
			$keys = array_keys($files);
			$res = $f3->get('DB')->exec("INSERT INTO uploads (path) VALUES (:path)", array(":path" => $keys[0]));
			$id = $f3->get('DB')->exec("SELECT id FROM uploads WHERE path = :path", array(":path" => $keys[0]));
			if(!$f3->get('AJAX')) {
				$f3->reroute("/".$id[0]['id']);
			} else {
				echo json_encode(array(
						"status" => "success",
						"timestamp" => time(),
						"message" => array(
							"id" => $id[0]['id']
						)
				));
			}
		} else {
			$mime = $web->mime($key);
			if ($mime != 'image/png' && $mime  != 'image/jpeg' && $mime  != 'image/gif') {
				if(!$f3->get('AJAX')) {
					$f3->set("ui_error_messages", array("File unsupported"));
					echo Template::instance()->render("home.html");
				} else {
					echo json_encode(array(
							"status" => "failed",
							"timestamp" => time(),
							"message" => array(
								"error" => "File unsupported"
							)
					));
				}
			}
			else { 
				if(!$f3->get('AJAX')) {
					$f3->set("ui_error_messages", array("Somethin went wrong"));
					echo Template::instance()->render("home.html");
				} else {
					echo json_encode(array(
							"status" => "failed",
							"timestamp" => time(),
							"message" => array(
								"error" => "Somethin went wrong"
							)
					));
				}
			}
		}
	}
});

$f3->route('GET /@id', function($f3) {
	$id = $f3->get('PARAMS.id');
	if(!is_numeric($id)) die("Must be a numeric value");
	$imgPath=$f3->get('DB')->exec("SELECT path FROM uploads WHERE id = :id", array(":id" => $id));
	if(count($imgPath) != 0) { 
		$f3->set("img_path", $imgPath[0]["path"]);
		echo Template::instance()->render("show.html");
	} else {
		$f3->set("ui_error_messages", array("Image not found"));
		echo Template::instance()->render("home.html");
	}
});

$f3->route('GET /install', function($f3) {
	if($f3->exists('installed') && !$f3->get("installed")) {
		$f3->get('DB')->exec("CREATE TABLE IF NOT EXISTS `uploads` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							  `path` varchar(255) NOT NULL,
							  PRIMARY KEY (`id`),
							  KEY `timestamp` (`timestamp`)
							) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;");
		$f3->reroute("/");
	} else {
		$f3->reroute("/");
	}
});

$f3->run();