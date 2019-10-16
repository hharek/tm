<?php
namespace TM\Type;

/**
 * Путь к файлу или каталогу
 */
class Path extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";

	public function check ($value) : bool
	{
		if (!is_string($value))
			throw new \Exception("Не является строкой.");

		/* Символ "." */
		if ($value === "." or $value === "/")
			return true;

		/* Срезаем символы слэша в начале и конце */
		if (mb_substr($value, 0, 1) === "/")
			$value = mb_substr($value, 1, mb_strlen($value) - 1);

		if (mb_substr($value, mb_strlen($value) - 1, 1) === "/")
			$value = mb_substr($value, 0, mb_strlen($value) - 1);

		/* Разбор */
		$value_ar = explode("/", $value);
		foreach ($value_ar as $part)
		{
			/* Указание в пути ".." и "." */
			if ($part === "." or $part === "..")
				throw new \Exception("Использовать имя файла как «..» и «.» запрещено.");

			/* Строка с начальными или конечными пробелами */
			if (mb_strlen($part) !== mb_strlen(trim($part)))
				throw new \Exception("Пробелы в начале или в конце имени файла.");

			/* Не указано имя файла */
			if (trim($part) === "")
				throw new \Exception("Не задано имя файла.");
		}

		return true;
	}
}
?>