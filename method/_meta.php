<?php
namespace TM\Method;

/**
 * Собираем информацию по таблице
 */
trait _Meta
{
	public static $schema = "public";
	public static $table;
	public static $name;
	public static $columns = [];

	/**
	 * Собираем информацию по таблице
	 *
	 * @return bool
	 */
	public static function _meta () : bool
	{
		return true;
	}

	/**
	 * Получить имя таблицы
	 *
	 * @param boolean $quotes
	 * @return string
	 */
	private static function _table(bool $quotes = false) : string
	{
		if ($quotes === false)
		{
			return static::$schema . "." . static::$table;
		}
		elseif ($quotes === true)
		{
			return '"' . static::$schema . '"."' . static::$table . '"';
		}
	}
}
?>