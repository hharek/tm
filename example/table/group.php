<?php
/**
 * Группа
 */
class Group extends TM
{
	/**
	 * Схема
	 * 
	 * @var string
	 */
	protected static $_schema = "core";

	/**
	 * Таблица
	 * 
	 * @var string
	 */
	protected static $_table = "group";
	
	/**
	 * Группа
	 * 
	 * @var string
	 */
	protected static $_name = "Группа";

	/**
	 * Поля
	 *
	 * @var array
	 */
	protected static $_field = 
	[
		[
			"identified" => "ID",
			"name" => "Порядковые номер",
			"type" => "id"
		],
		[
			"identified" => "Name",
			"name" => "Наименование",
			"type" => "string",
			"unique" => true,
			"order" => "asc"
		],
		[
			"identified" => "Active",
			"name" => "Активность",
			"type" => "boolean",
			"default" => false
		]
	];
}
?>