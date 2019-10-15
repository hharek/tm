<?php
use \TM\Type;

/**
 * Лог. Таблица без первичного ключа
 */
class Log extends TM\Table
{
	public static $table = "log";
	public static $name = "Отчёт";
	public static $columns = [];
}

$c = new Type\Text();
$c->column = "Content";
$c->name = "Содержание";
Log::$columns[] = $c;

$c = new Type\Datetime();
$c->column = "Date";
$c->name = "Дата";
Log::$columns[] = $c;


/**
 * Глоссарий. Первичный ключ не int
 */
class Glossary extends TM\Table
{
	public static $table = "glossary";
	public static $name = "Глоссарий";
	public static $columns = [];
}

$c = new Type\Str();
$c->column = "Word";
$c->name = "Слово";
$c->primary = true;
Glossary::$columns[] = $c;

$c = new Type\Text();
$c->column = "Content";
$c->name = "Описание";
Glossary::$columns[] = $c;
?>