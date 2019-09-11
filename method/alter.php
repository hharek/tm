<?php
namespace TM\Method;

/**
 * Изменить структуру таблицы
 * Выполнить ALTER на основании данных класса и таблице в базе
 */
trait Alter
{
	use \TM\Table_Params,
		_Table,
		TCheck,
		_Meta,
		Debug,
		DB_Conn;

	/**
	 * Изменить структуру таблицы
	 * Можно указать какие колонки изменить и как. Доступные действия: "add", "alter", "drop".
	 * Если нужно класс сравнить с таблицой отличной от static::$table, то укажите аргумент $table
	 *
	 * @param array|null $columns
	 * @param string|null $table
	 * @example alter(), alter(["Name", "Price"]), alter(["Name" => "add", "Active" => "alter"])
	 */
	public static function alter (array $columns = null, string $table = null)
	{
		/* Проверяем правильность заполнение класса */
		static::tcheck();

		/* Собираем мету */
		static::_meta();
	}
}
?>