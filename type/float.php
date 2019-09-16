<?php
namespace TM\Type;

/**
 * Число с плавающей запятой
 */
class _Float extends \TM\Column
{
	public $type_sql = "float";
	public $type_php = "float";

	public static function verify(array $info, string $table): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-numeric#DATATYPE-FLOAT */

		if (in_array($info['data_type'], ["real", "double precision"]))
			return true;

		return false;
	}
}

/* Алиас */
class Double extends _Float {}
class Real extends _Float {}
?>