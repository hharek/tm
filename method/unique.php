<?php
namespace TM\Method;

/**
 * Проверка на уникальность
 */
trait Unique
{
	use \TM\Table_Params,
		_Meta;

	/**
	 * Проверка на уникальность
	 *
	 * @param array $data
	 * @param $primary
	 */
	public static function unique(array $data, $primary = null)
	{
		/* Проверка */
		if (empty($data))
			throw new \TM\Exception("Не указаны данные для проверки уникальности.", static::$schema, static::$table, static::$name);

		static::check($data);

		/* Мета */
		static::_meta();
	}
}
?>