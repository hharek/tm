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
catch (\TM\Exception_Many $ex)
{
	foreach ($ex->getErr() as $e)
		echo $e->getName() . ". Поле «" . $e->getColumn()->name . "» задано неверно. " . $e->getError() . "\n";
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