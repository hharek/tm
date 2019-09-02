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
	use Method\_Meta,
		Method\TCheck,
		Method\Create;

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

	/**
	 * Сведения по первичному ключу
	 * Может быть только один столбец
	 *
	 * @var Column
	 */
	protected static $_primary;

	/**
	 * Уникальные ключи
	 *
	 * @var Column[]
	 */
	protected static $_unique = [];

	/**
	 * Столбцы используемые по умолчанию для сортировки выборки
	 *
	 * @var Column[]
	 */
	protected static $_order = [];
}
?>