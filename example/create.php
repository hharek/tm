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

//\TM\Table::debug();

Category::create();
Product::create();
Stock::create();
Product_Stock::create();

Glossary::create(false, false);
Log::create(true, false);
?>