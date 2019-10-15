<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/product.php";

$db = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWORD);
\TM\Table::db_conn($db);

echo \TM\Table::show_init("public", "product");

?>