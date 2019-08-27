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
}
?>