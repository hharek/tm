<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

try
{
	Product::check
	([
		"Name" => "<test>",
		"Category_ID" => "test"
	]);
}
catch (\TM\Exception $e)
{
	echo $e->getName() . ". " . $e->getColumn()->name . ". " . $e->getError();
}
catch (Exception $e)
{
	echo $e->getMessage();
}




?>