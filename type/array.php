<?php
namespace TM\Type;

/**
 * Массив
 */
class _Array extends \TM\Column
{
	public $type_sql = "jsonb";
	public $type_php = "array";
	public $lite = false;

	public static function verify(array $info): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-json */

		if (\TM\PGSQL_JSON_VERIFY_TYPE !== "array")
			return false;

		if (in_array($info['data_type'], ["json", "jsonb"]))
			return true;

		return false;
	}
}

/* Алиас */
class Arr extends _Array {};
?>