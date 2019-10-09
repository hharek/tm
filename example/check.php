<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

try
{
	Product::check
	([
		"Name" => "<test>",			/* Ошибка. HTML-символы */
		"Price" => "test"			/* Ошибка. Ожидает unsigned int */
	]);
}
catch (\TM\Exception_Many $e)
{
	print_r($e);
}
catch (\TM\Exception $e)
{
	echo $e->getName() . ". Поле «" . $e->getColumn()->name . "» задано неверно. " . $e->getError();
}
catch (Exception $e)
{
	echo $e->getMessage();
}




?>