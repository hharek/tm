<?php
use TM\Type;

/**
 * Склад
 */
class Stock extends TM\Table
{
	public static $table = "stock";
	public static $name = "Склад";
	public static $columns = [];
}

$c = new Type\ID();
$c->column = "ID";
$c->name = "Порядковый номер";
Stock::$columns[] = $c;

$c = new Type\Str();
$c->column = "Name";
$c->name = "Наименование";
$c->unique = true;
Stock::$columns[] = $c;
?>