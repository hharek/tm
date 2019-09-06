<?php
namespace TM\Method;

/**
 * Собираем информацию по таблице
 */
trait _Meta
{
	use \TM\Table_Params;

	/**
	 * Сведения по первичному ключу
	 * Может быть только один столбец
	 *
	 * @var \TM\Column
	 */
	protected static $_primary;

	/**
	 * Уникальные ключи
	 *
	 * @var \TM\Column[]
	 */
	protected static $_unique = [];

	/**
	 * Столбцы используемые по умолчанию для сортировки выборки
	 *
	 * @var \TM\Column[]
	 */
	protected static $_order = [];

	/**
	 * Собираем информацию по таблице
	 */
	private static function _meta ()
	{
		/* Первичные ключи */
		foreach (static::$columns as $c)
		{
			if ($c->primary)
			{
				static::$_primary = $c;
				break;
			}
		}

		/* Уникальные ключи */
		$key_num = 1;
		foreach (static::$columns as $c)
		{
			if ($c->unique)
			{
				$key = $c->unique_key ?? "UN_" . $key_num;

				if (empty(static::$_unique[$key]))
					static::$_unique[$key] = [];

				static::$_unique[$key][] = $c;
			}
		}

		/* Столбцы сортировки */
		foreach (static::$columns as $c)
		{
			if ($c->order)
				static::$_order[$c->order_index] = $c;
		}
		ksort(static::$_order);
	}
}
?>