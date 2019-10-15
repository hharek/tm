<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

try
{
	Product::unique
	([
		"Name" => "Товар 1",
		"Articul" => "Т00001",
		"Category_ID" => 1,
	]);
}
catch (\TM\Exception_Many $ex)
{
	foreach ($ex->getErr() as $e)
		echo $e->getName() . ". Поле «" . $e->getColumn()->name . "» не уникально. \n";
}
catch (\TM\Exception $e)
{
	echo $e->getName() . ". Поле «" . $e->getColumn()->name . "» не уникально. " . $e->getError();
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>