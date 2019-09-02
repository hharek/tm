<?php
use \TM\Type;

/**
 * Категория
 */
class Category extends TM\Table
{
	public static $table = "category";
	public static $name = "Категория";
	public static $columns = [];
}

/* Поля */
$c = new Type\ID();
$c->column = "ID";
$c->name = "Порядковый номер";
Category::$columns[] = $c;

$c = new Type\Str();
$c->column = "Name";
$c->name = "Наименование";
$c->unique = true;
Category::$columns[] = $c;
?>