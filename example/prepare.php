<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

$db = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWORD);
\TM\Table::db_conn($db);

try
{

	$data = Product::prepare
	([
		"Name" => "Товар-1",
		"Category_ID" => 1,
		"Articul" => "A-100001",				// "a-100001". Т.к. prepare = "strtolower"
		"Price" => "100,01",					// "100.10". Т.к. prepare = Price::prepare
		"Last_Modufied" => new DateTime(),		// "now()". Т.к. prepare = function () ...
		"Active" => true						// "1"
	]);

	pg_insert($db, Product::$table, $data);

}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>