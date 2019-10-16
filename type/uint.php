<?php
namespace TM\Type;

/**
 * Число без знака
 */
class UInt extends _Int
{
	public function check ($value) : bool
	{
		parent::check($value);

		if ((int)$value < 0)
			throw new \Exception("Отрицательное число.");

		return true;
	}
}
?>