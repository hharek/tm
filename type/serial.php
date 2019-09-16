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
		$default =
<<<SQL
nextval('"{table}_{column}_seq"'::regclass)
SQL;

		$default = strtr($default,
		[
			"{table}" => $table,
			"{column}" => $info['column_name']
		]);

		if ($info['column_default'] === $default)
			return true;

		return false;
	}
}
?>