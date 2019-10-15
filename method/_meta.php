<?php
namespace TM\Method;

/**
 * Собираем информацию по таблице
 */
trait _Meta
{
	use \TM\Table_Params;

	/**
	 * Поля входящие в первичный ключ
	 *
	 * @var \TM\Column[]
	 */
	protected static $_primary = [];

	/**
	 * Уникальные ключи
	 *
	 * @var \TM\Column[]
	 */
	protected static $_unique = [];

	/**
	 * Неуникальные индексы
	 *
	 * @var \TM\Column[]
	 */
	protected static $_index = [];

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
		/* Обнуляем */
		static::$_primary = [];
		static::$_unique = [];
		static::$_index = [];
		static::$_order = [];

		/* Первичные ключи */
		foreach (static::$columns as $c)
		{
			if ($c->primary)
			{
				static::$_primary[] = $c;
			}
		}

		/* Уникальные ключи */
		$key_num = 1;
		foreach (static::$columns as $c)
		{
			if ($c->unique)
			{
				$key = $c->unique_key ?? "TM_UN_" . $key_num;
				$key_num++;

				if (empty(static::$_unique[$key]))
					static::$_unique[$key] = [];

				static::$_unique[$key][] = $c;
			}
		}
		static::$_unique = array_values(static::$_unique);

		/* Неуникальные индексы */
		$key_num = 1;
		foreach (static::$columns as $c)
		{
			if ($c->index)
			{
				$key = $c->index_key ?? "TM_IDX_" . $key_num;
				$key_num++;

				if (empty(static::$_index[$key]))
					static::$_index[$key] = [];

				static::$_index[$key][] = $c;
			}
		}
		static::$_index = array_values(static::$_index);

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