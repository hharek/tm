<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

$db = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWORD);
\TM\Table::db_conn($db);

try
{
	$result = pg_query($db, 'SELECT * FROM "product" LIMIT 1');
	$data = pg_fetch_assoc($result);

	$data = Product::process($data);
	var_dump($data);
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>