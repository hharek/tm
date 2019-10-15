<?php
namespace TM;

/**
 * Основные параметры таблицы
 */
trait Table_Params
{
	/**
	 * Наименование схемы
	 *
	 * @var string
	 */
	public static $schema = "public";

	/**
	 * Наименование таблицы в базе
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * Наименование
	 * Добавляется в комментарий к таблице и используется при выводе сообщений об ошибках
	 *
	 * @var string
	 */
	public static $name;

	/**
	 * Столбцы таблицы. Массив объектов Column
	 *
	 * @var Column[]
	 */
	public static $columns = [];
}

/**
 * Таблица
 * Основной класс в TM. Каждая таблица реализуется классом-потомком этого класса.
 */
class Table
{
	use Table_Params,
		Method\_Table,
		Method\_Meta,
		Method\TCheck,
		Method\Create,
		Method\Debug,
		Method\_Info,
		Method\Show_Init,
		Method\Check,
		Method\Unique;
}
?>