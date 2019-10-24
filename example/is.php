<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";
require "table/stock.php";
require "table/product_stock.php";
require "table/not_normalized.php";

$db = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWORD);
\TM\Table::db_conn($db);

try
{
	Product::is(100);
	Product::is(["ID" => 100]);

//	Product_Stock::is(1);				/* Ошибка. Составной первичный ключ */
	Product_Stock::is(["Product_ID" => 100, "Stock_ID" => 1]);

//	Log::is(1); 						/* Ошибка. Отсутствует первичный ключ */
	Glossary::is("Один", false);
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>