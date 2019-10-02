<?php
namespace TM\Type;

/**
 * Последовательность. Целое число с автоувеличением
 * int NOT NULL DEFAULT nextval('имя_таблицы_имя_столбца_seq')
 */
class Serial extends UInt
{
	public $type_sql = "serial";
	public $require = false;

	public static function verify(array $info, string $table): bool
	{
		$default_sql =
<<<SQL
nextval('"{$table}_{$info['column_name']}_seq"'::regclass)
SQL;

		if ($info['column_default'] === $default_sql)
			return true;

		return false;
	}
}
?>