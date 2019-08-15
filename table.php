<?php
namespace TM;

/**
 * Таблица
 * Основной класс в TM. Каждая таблица реализуется классом-потомком этого класса.
 *
 * @package TM
 */
class Table
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
?>