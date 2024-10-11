<?php
require_once __DIR__ . '/envconfig.php';

if (!defined('ENV')) define('ENV', "local");

if (ENV == "local") :

    if (!defined('DB_HOST')) define('DB_HOST', Env::get('LOCAL_DB_HOST'));
    if (!defined('DB_NAME')) define('DB_NAME', Env::get('LOCAL_DB_NAME'));
    if (!defined('DB_USER')) define('DB_USER', Env::get('LOCAL_DB_USER'));
    if (!defined('DB_PASS')) define('DB_PASS', Env::get('LOCAL_DB_PASS'));

endif;

try {
    global $dbh;  // Make the $dbh global
    $dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}