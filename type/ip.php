<?php
namespace TM\Type;

/**
 * IP-адрес.
 * Можно использовать также IPv6 адрес
 */
class IP extends \TM\Column
{
	public $type_sql = "inet";
	public $type_php = "string";

	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_string($value))
			throw new \Exception("Не является строкой.");

		if (!filter_var($value, FILTER_VALIDATE_IP))
			throw new \Exception("Не является IP адресом.");

		return true;
	}

	public static function verify(array $info): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-net-types */

		if (in_array($info['data_type'], ["inet", "cidr"]))
			return true;

		return false;
	}
}
?>