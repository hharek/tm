<?php
namespace TM\Type;

/**
 * Столбец сортировки
 * По умолчанию вставляет серийный номер. Работает только при наличие поля типа «ID»
 * int NOT NULL DEFAULT currval('имя_таблицы_имя_столбца_id_seq')
 */
class Order extends UInt
{
	public $require = false;
	public $default_sql =
<<<SQL
currval('"{table}_ID_seq"'::regclass)
SQL;

	public static function verify (array $info, string $table) : bool
	{
		$o = new Order();
		$default_sql = $o->default_sql;
		$default_sql = strtr($default_sql, ["{table}" => $table]);

		if ($info['column_default'] === $default_sql)
			return true;

		return false;
	}
}
?>