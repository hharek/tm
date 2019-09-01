<?php
namespace TM\Method;

/**
 * Собираем информацию по таблице
 */
trait _Table
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