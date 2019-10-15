<?php
use TM\Type;

/**
 * Кол-во товара на складе
 */
class Product_Stock extends TM\Table
{
	public static $table = "product_stock";
	public static $name = "Кол-во товара на складе";
	public static $columns = [];
}

$c = new Type\UInt();
$c->column = "Product_ID";
$c->name = "Товар";
$c->primary = true;
Product_Stock::$columns[] = $c;

$c = new Type\UInt();
$c->column = "Stock_ID";
$c->name = "Склад";
$c->primary = true;
Product_Stock::$columns[] = $c;

$c = new Type\UInt();
$c->column = "Count";
$c->name = "Кол-во";
Product_Stock::$columns[] = $c;
?>