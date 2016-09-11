<?php
/**
 * Пользователь
 */
class User extends TM
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
	protected static $_table = "user";
	
	/**
	 * Комментарий
	 * 
	 * @var string
	 */
	protected static $_name = "Пользователь";

	/**
	 * Поля
	 * 
	 * @var array
	 */
	protected static $_field = 
	[
		[
			"identified" => "ID",
			"name" => "Порядковый номер",
			"type" => "id"
		],
		[
			"identified" => "Email",
			"name" => "Почтовый ящик",
			"type" => "email",
			"unique" => true,
			"order" => "asc"
		],
		[
			"identified" => "Name",
			"name" => "Имя",
			"type" => "string",
			"unique" => true
		],
		[
			"identified" => "Password",
			"name" => "Пароль",
			"type" => "string"
		],
		[
			"identified" => "Group_ID",
			"name" => "Привязка к группе",
			"type" => "uint",
			"foreign" => ["group", "ID"]
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