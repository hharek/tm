<?php
namespace TM\Method;

/**
 * Собираем информацию по таблице
 */
trait _Meta
{
	/**
	 * @var string
	 */
	public static $schema = "public";

	/**
	 * @var string
	 */
	public static $table;

	/**
	 * @var string
	 */
	public static $name;

	/**
	 * @var \TM\Column[]
	 */
	public static $columns = [];

	/**
	 * @var \TM\Column
	 */
	private static $_primary = [];

	/**
	 * @var \TM\Column[]
	 */
	private static $_unique = [];

	/**
	 * Собираем информацию по таблице
	 */
	public static function _meta ()
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
	}

	/**
	 * Получить имя таблицы
	 *
	 * @param boolean $quotes
	 * @return string
	 */
	private static function _table(bool $quotes = false) : string
	{
		if ($quotes)
		{
			return static::$schema . "." . static::$table;
		}
		else
		{
			return '"' . static::$schema . '"."' . static::$table . '"';
		}
	}
}
?>