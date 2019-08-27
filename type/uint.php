<?php
namespace TM\Type;

/**
 * Число без знака
 */
class UInt extends _Int
{
	public static function check ($value, \TM\Column $column = null) : bool
	{
		_Int::check($value, $column);

		if ((int)$value < 0)
			throw new \Exception("Отрицательное число.");

		return true;
	}
}
?>