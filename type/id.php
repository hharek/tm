<?php
namespace TM\Type;

/**
 * Порядковый номер. Первичный ключ
 * Рекомендуется всегда добавлять в таблицу
 */
class ID extends Serial
{
	public $primary = true;

	public static function verify (array $info, string $table) : bool
	{
		if (Serial::verify($info, $table) && $info['primary'] === true)
			return true;

		return false;
	}
}
?>