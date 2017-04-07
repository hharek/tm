# TM

Table Manager for PostgreSQL

Requirements: PHP 7.0.10 and higher. PHP-module: pgsql

-------------------------------------------------------------------------------------

Генератор SQL-запросов для таблиц в СУБД PostgreSQL.

Требования: PHP 7.0.10 и выше. PHP-модуль: pgsql

### Описание
С помощью TM можно выполнять популярные запросы по таблице, такие как INSERT, UPDATE, DELETE, SELECT. Просто перед тем как работать с таблицей, создайте класс потомок класса TM, описывающий эту таблицу. Все данные, которые будут поступать в метод будут проверяться на соответствие типу и в случае несоответствия выкидывать исключение. Также все SQL-запросы выполняются через параметризованный запрос, что позволяет избежать SQL-инъекций. Результатом запросов типа SELECT будут массивы элементы, которого будут иметь соответствующий, т.е. если поле в таблице имеет тип «boolean» со значением «true», то элемент массива будет иметь тип (bool)true, а не (string)'t'. Также для запросов типа SELECT можно использовать, SQL-операторы: IN, !=, >=, >, LIKE и др. TM поддерживает множество типов не соответствующие SQL-типам, к примеру «email» или «identified» и вы также можете создать свой тип или модифицировать существующий.

### Особенности
* Простота работы по сравнению с обычными SQL запросами
* Валидатор данных
* Защита от SQL-инъекций
* Приведение SQL типов к PHP типу
* Подготовка данных перед SQL-запросом
* Поддержка разных SQL-операторов для SELECT: «IN», «LIKE», «>=» и др.
* Поддержка NULL запросы типа «= null» преобразуется в «is null»
* Не является ORM, поэтому быстрее и менее затратное из-за минимум абстракций

### Пример
```php
<?php
/* TM */
require "tm.php";
require "tm_type.php";

/* Класс-таблица */
class Product extends TM
{
	public static $name = "Товар";
	public static $table = "product";
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
			"unique" => true
		]
	];
}

try
{
	/* Создаём ресурс соединения к БД и назначаем классу TM */
	TM::set_db_conn(pg_connect("host=127.0.0.1 port=5432 dbname=example user=example password=pass"));
	
	/* Проверяем правильность проверки заполнения класса (необязательно) */
	Product::check_struct();
	
	/* Создание таблицы (единожды) */
	Product::create();
	
	/* Модифицируем данные таблицы */
	$product_1 = Product::insert(["Name" => "Товар 1"]);
	$product_2 = Product::insert(["Name" => "Товар 2"]);
	$product_3 = Product::insert(["Name" => "Товар 3"]);
	$product_2_new = Product::update(["Name" => "Товар 2. Изменённый"], $product_2['ID']);
	$product_3_delete = Product::delete($product_3['ID']);
	
	/* Выборки */
	echo Product::count();		
	print_r(Product::get(1));
	print_r(Product::select());
	print_r(Product::select(["Name" => "Товар 1"]));
	print_r(Product::select(["Name" => ["ilike", "тов%"]]));
}
catch (Exception_Many $e)
{
	print_r($e->get_err());
}
catch (Exception $e)
{
	echo $e->__toString();
}
?>
```

### Wiki
- [Атрибуты поля](https://github.com/hharek/tm/wiki/%D0%90%D1%82%D1%80%D0%B8%D0%B1%D1%83%D1%82%D1%8B-%D0%BF%D0%BE%D0%BB%D1%8F)
- [Методы](https://github.com/hharek/tm/wiki/%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B)

### Разработка
- Список изменений ([CHANGELOG](https://github.com/hharek/tm/wiki/CHANGELOG))
- Дорожная карта ([ROADMAP](https://github.com/hharek/tm/wiki/ROADMAP))

