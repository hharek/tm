<?php
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
$column = new \TM\Types\Id();
$column->column = "ID";
$column->name = "Порядковый номер";
Category::$columns[] = $column;

$column = new \TM\Types\Str();
$column->column = "Name";
$column->name = "Наименование";
Category::$columns[] = $column;
?>