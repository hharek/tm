<?php
namespace TM\Type;

/**
 * Цена - два числа после точки всегда положительная
 */
class Price extends \TM\Column
{
	public $type_sql = "money";
	public $type_php = "float";
	public $index = true;

	public function check ($value) : bool
	{
		if (is_string($value))
			$value = str_replace(",", ".", $value);

		if (!is_numeric($value))
			throw new \Exception("Не является числом.");

		if ((int)$value < 0)
			throw new \Exception("Отрицательное число.");

		return true;
	}

	public function prepare ($value) : string
	{
		if (is_string($value))
			return str_replace(",", ".", $value);
		else
			return (string)$value;
	}

	public static function verify (array $info, string $table) : bool
	{
		if ($info['data_type'] === "money")
			return true;

		if ($info['data_type'] === "numeric" && (int)$info['numeric_scale'] === 2)
			return true;

		return false;
	}
}
?>