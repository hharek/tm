<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";
require "table/stock.php";
require "table/product_stock.php";


/**
 * T1
 */
class T1 extends TM\Table
{
	public static $table = "t1";
	public static $name = "T1";
	public static $columns = [];
}

$c = new \TM\Type\ID();
$c->column = "ID";
$c->name = "Порядковый номер";
T1::$columns[] = $c;

$c = new \TM\Type\Date();
$c->column = "Date";
$c->name = "Дата";
$c->prepare = function ($value) use ($c)
{

};
T1::$columns[] = $c;


try
{


	$data = T1::_prepare(["Date" => "12.02.2019"]);

	print_r($data);


}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>