<?php
namespace TM\Type;

/**
 * Булёвое значение
 */
class _Bool extends \TM\Column
{
	public $type_sql = "boolean";
	public $type_php = "boolean";

	public static function verify(array $info): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-boolean */

		if ($info['data_type'] !== "boolean")
			return false;

		return true;
	}
}

/* Алиас */
class Boolean extends _Bool {}
?>