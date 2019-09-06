<?php
namespace TM\Method;

/**
 * Получить имя таблицы
 */
trait _Table
{
	use \TM\Table_Params;

	/**
	 * Получить имя таблицы
	 *
	 * @param boolean $quotes
	 * @return string
	 */
	private static function _table(bool $quotes = false) : string
	{
		if ($quotes)
			return '"' . static::$schema . '"."' . static::$table . '"';
		else
			return static::$schema . "." . static::$table;
	}
}
?>