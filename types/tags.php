<?php
namespace TM\Types;

/**
 * Строка с тэгами через запятую
 */
class Tags extends Text
{
	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_scalar($value))
			throw new \Exception("Не является строкой.");
		$value = (string)$value;

		/* В нижний регистр */
		$value = mb_strtolower($value);

		/* Проверка на наличие недопустимых символов */
		$value_trim = strtr
		(
			$value,
			[
				'0'=>'', '1'=>'', '2'=>'', '3'=>'', '4'=>'', '5'=>'', '6'=>'', '7'=>'', '8'=>'', '9'=>'',
				'a'=>'', 'b'=>'', 'c'=>'', 'd'=>'', 'e'=>'', 'f'=>'', 'g'=>'', 'h'=>'', 'i'=>'', 'j'=>'',
				'k'=>'', 'l'=>'', 'm'=>'', 'n'=>'', 'o'=>'', 'p'=>'', 'q'=>'', 'r'=>'', 's'=>'', 't'=>'',
				'u'=>'', 'v'=>'', 'w'=>'', 'x'=>'', 'y'=>'', 'z'=>'',
				'а'=>'', 'б'=>'', 'в'=>'', 'г'=>'', 'д'=>'', 'е'=>'', 'ё'=>'', 'ж'=>'', 'з'=>'', 'и'=>'',
				'й'=>'', 'к'=>'', 'л'=>'', 'м'=>'', 'н'=>'', 'о'=>'', 'п'=>'', 'р'=>'', 'с'=>'', 'т'=>'',
				'у'=>'', 'ф'=>'', 'х'=>'', 'ц'=>'', 'ч'=>'', 'ш'=>'', 'щ'=>'', 'ъ'=>'', 'ы'=>'', 'ь'=>'',
				'э'=>'', 'ю'=>'', 'я'=>'',
				','=>'', '-', ' '=>''
			]
		);

		if ($value_trim !== "")
			throw new \Exception("Допускаются символы: 0-9,a-z,а-я,«,».");

		/* Проверка тэгов на пустоту */
		$tags = explode(",", $value);
		if (empty($tags))
			throw new \Exception("Не указано ни одного тэга");

		foreach ($tags as $key => $val)
		{
			$val = trim($val);
			if (empty($val))
				throw new \Exception("Пустой тэг или лишняя зяпятая");

			$tags[$key] = $val;
		}

		/* Повторяющиеся тэги */
		if (count($tags) !== count(array_unique($tags)))
			throw new \Exception("Повторяющиеся тэги");

		return true;
	}
}
?>