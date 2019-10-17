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
$c->require = false;
$c->null = true;
$c->empty = true;
$c->prepare = "strtolower";		/* Значение в SQL-запросе будет в нижнем регистре */
Product::$columns[] = $c;

$c = new Type\Str();
$c->column = "Name";
$c->name = "Наименование";
$c->unique = true;
$c->unique_key = "Name_UN";		/* UNIQUE ("Name", "Category_ID") */
Product::$columns[] = $c;

$c = new Type\UInt();
$c->column = "Category_ID";
$c->name = "Категория";
$c->unique = true;
$c->unique_key = "Name_UN";		/* UNIQUE ("Name", "Category_ID") */
$c->null = true;
Product::$columns[] = $c;

$c = new Type\Price();
$c->column = "Price";
$c->name = "Цена";
$c->require = false;
$c->default = 0.00;
$c->index = true;
Product::$columns[] = $c;

$c = new Type\Order();
$c->column = "Order";
$c->name = "Сортировка";
Product::$columns[] = $c;

$c = new Type\Datetime();
$c->column = "Last_Modufied";
$c->name = "Дата последнего изменения";

$c->check = function ($value) use ($c) : bool			/* Своя функция проверки. Работаем только с объектом Datetime */
{
	if (!($value instanceof DateTime))
		throw new Exception("Не является объектом класса Datetime");

	return true;
};

$c->prepare = function (DateTime $value) use ($c) : string		/* Своя функция обработки перед запросом. Работаем только с объектом Datetime */
{
	return $value->format($c->datetime_format_sql);
};

$c->process = function (string $value)				/* Своя функция обработки значения запроса. Возращаяем объект Datetime вместо строки */
{
	return new Datetime($value);
};

Product::$columns[] = $c;

$c = new Type\Boolean();
$c->column = "Active";
$c->name = "Активность";
$c->require = false;
$c->default = false;
Product::$columns[] = $c;
?>