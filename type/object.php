<?php
namespace TM\Type;

/**
 * Объект класса stdClass
 */
class _Object extends \TM\Column
{
	public $type_sql = "jsonb";
	public $type_php = "object";
	public $lite = false;

	public static function verify (array $info, string $table) : bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-json */

		if (\TM\PGSQL_JSON_VERIFY_TYPE !== "object")
			return false;

		if (in_array($info['data_type'], ["json", "jsonb"]))
			return true;

		return false;
	}
}

/* Алиас */
class Obj extends _Object {};
?>