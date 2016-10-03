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
	protected static $_table = "product";
	
	/**
	 * Товар
	 * 
	 * @var string
	 */
	protected static $_name = "Товар";
	
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
			"identified" => "Active",
			"name" => "Активность",
			"type" => "boolean",
			"default" => true
		]
	];
}
?>