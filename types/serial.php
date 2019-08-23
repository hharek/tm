<?php
namespace TM\Types;

/**
 * Последовательность. Целое число с автоувеличением
 * int NOT NULL DEFAULT nextval('имя_таблицы_имя_столбца_seq')
 */
class Serial extends UInt
{
	public $type_sql = "serial";
	public $require = false;
}
?>