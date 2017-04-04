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
	public static $table = "category";
	
	/**
	 * Наименование
	 * 
	 * @var string
	 */
	public static $name = "Категория";

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
			"identified" => "Content",
			"name" => "Описание",
			"type" => "html",
			"empty_allow" => true,
			"require" => false
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
			"unique" => true,
			"unique_key" => ["UN_Name", "UN_Url"],
			"foreign" => 
			[
				"table" => "category",
				"field" => "ID",
				"class" => "Category"
			]
		],
		[
			"identified" => "Priority",
			"name" => "Приоритет",
			"type" => "serial"
		],
		[
			"identified" => "Color",
			"name" => "Цвет",
			"type" => "enum",
			"enum_values" => ["красный", "зелёный", "синий"],
			"default" => "зелёный"
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