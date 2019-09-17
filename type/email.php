<?php
namespace TM\Type;

/**
 * Почтовый ящик
 */
class Email extends \TM\Column
{
	public $type_sql = "varchar(127)";
	public $type_php = "string";
	public $prepare = "strtolower";
	public $unique = true;

	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_string($value))
			throw new \Exception("Не является строкой.");

		if (!filter_var($value, FILTER_VALIDATE_EMAIL))
			throw new \Exception("Не является почтовым ящиком.");

		return true;
	}

	public static function verify(array $info, string $table): bool
	{
		if (!in_array($info['data_type'], ["character varying", "character"]))
			return false;

		if (stripos($info['column_name'], "email") === false && stripos($info['column_name'], "e-mail") === false)
			return false;

		return true;
	}
}
?>