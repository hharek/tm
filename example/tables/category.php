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
$column = new \TM\Type\Id();
$column->column = "ID";
$column->name = "Порядковый номер";
Category::$columns[] = $column;

$column = new \TM\Type\Str();
$column->column = "Name";
$column->name = "Наименование";
$column->unique = true;
Category::$columns[] = $column;
?>