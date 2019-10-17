<?php
namespace TM\Method;

/**
 * Обрабатываем данные после запроса
 */
trait Process
{
	use \TM\Table_Params,
		_Meta;

	/**
	 * Обрабатываем данные после запроса
	 *
	 * @param array $data
	 */
	public static function process (array $data) : array
	{
		static::_meta();

		return $data;
	}
}
?>