<?php
use TM\Type;

/**
 * Товар
 */
class Product extends TM\Table
{
	public static $table = "product";
	public static $name = "Товар";
	public static $columns = [];
}

$c = new Type\ID();
$c->column = "ID";
$c->name = "Порядковый номер";
Product::$columns[] = $c;

$c = new Type\Str();
$c->column = "Articul";
$c->name = "Артикул";
$c->unique = true;
Product::$columns[] = $c;

$c = new Type\Str();
$c->column = "Name";
$c->name = "Наименование";
$c->unique = true;
$c->unique_key = "Name_UN";
Product::$columns[] = $c;

$c = new Type\UInt();
$c->column = "Category_ID";
$c->name = "Категория";
$c->unique = true;
$c->unique_key = "Name_UN";
Product::$columns[] = $c;

$c = new Type\Price();
$c->column = "Price";
$c->name = "Цена";
Product::$columns[] = $c;
?>