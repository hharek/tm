<?php
/**
 * Товар
 */
class Product extends TM
{
	/**
	 * Таблица
	 * 
	 * @var string
	 */
	public static $table = "product";
	
	/**
	 * Наименование таблицы
	 * 
	 * @var string
	 */
	public static $name = "Товар";
	
	/**
	 * Поля
	 * 
	 * @var array
	 */
	public static $fields = 
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
			"identified" => "Content",
			"name" => "Содержание",
			"type" => "html",
			"null" => true,
			"require" => false
		],
		[
			"identified" => "Price",
			"name" => "Цена",
			"type" => "price"
		],
		[
			"identified" => "Category_ID",
			"name" => "Привязка к категории",
			"type" => "int",
			"foreign" => 
			[
				"table" => "category",
				"field" => "ID",
				"class" => "Category"
			],
			"unique" => true,
			"unique_key" => ["UN_Name", "UN_Url"]
		],
		[
			"identified" => "Sort",
			"name" => "Сортировка",
			"type" => "order",
			"order_where" => "Category_ID"
		],
		[
			"identified" => "Active",
			"name" => "Активность",
			"type" => "boolean",
			"default" => true
		]
	];
}
?>