<?php
require 'environment.php';

global $config;
$config = array();
if(ENVIRONMENT == 'development') {
	define("BASE_URL", "http://projetox.pc/devstagram-api/");
	$config['dbname'] = 'devstagram';
	$config['host'] = 'localhost';
	$config['dbuser'] = 'jefferson';
	$config['dbpass'] = '12345';
	$config['jwt_secret_key'] = 'abcd12345';
} else {
	define("BASE_URL", "http://localhost/devstagram-api/");
	$config['dbname'] = 'devstagram';
	$config['host'] = 'localhost';
	$config['dbuser'] = 'root';
	$config['dbpass'] = 'root';
	$config['jwt_secret_key'] = 'abcd12345';
}

global $db;
try {
	$db = new PDO("mysql:dbname=".$config['dbname'].";host=".$config['host'], $config['dbuser'], $config['dbpass']);
} catch(PDOException $e) {
	echo "ERRO: ".$e->getMessage();
	exit;
}