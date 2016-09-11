<?php
/**
 * Категория
 */
class Category extends TM
{
	/**
	 * Таблица
	 * 
	 * @var string
	 */
	protected static $_table = "category";
	
	/**
	 * Наименование
	 * 
	 * @var string
	 */
	protected static $_name = "Категория";

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
			"identified" => "Name",
			"name" => "Наименование",
			"type" => "string",
			"unique" => true,
			"unique_key" => "UN_Name"
		],
		[
			"identified" => "Url",
			"name" => "Урл",
			"type" => "url_part",
			"unique" => true,
			"unique_key" => "UN_Url"
		],
		[
			"identified" => "Order",
			"name" => "Сортировка",
			"type" => "order",
			"order" => "asc"
		],
		[
			"identified" => "Parent",
			"name" => "Корень",
			"type" => "int",
			"null" => true,
			"foreign" => ["category", "ID"],
			"unique" => true,
			"unique_key" => ["UN_Name", "UN_Url"]
		],
	];
}
?>