<?php
header("Content-type: text/plain; charset=UTF-8");

require "../tm.php";
require "../tm_type.php";

require "table/category.php";
require "table/product.php";

$db = pg_connect("host=127.0.0.1 port=5432 dbname=example user=example password=pass");
TM::set_db_conn($db);

try
{
	/* Проверяем правильность проверки заполнения класса Category и Product */
	Category::check_struct();
	Product::check_struct();
	
	/* Создаём таблицу */
	Category::create(true);
	Product::create(true);
	
	/* INSERT category */
	$c1 = Category::insert(["Name" => "Категория 1", "Url" => "категория-1", "Parent" => null]);
	$c2 = Category::insert(["Name" => "Категория 2", "Url" => "категория-2", "Parent" => null]);
	$c3 = Category::insert(["Name" => "Категория 3", "Url" => "категория-3", "Parent" => null]);
	$c4 = Category::insert(["Name" => "Категория 4", "Url" => "категория-4", "Parent" => 1]);
	$c5 = Category::insert(["Name" => "Категория 5", "Url" => "категория-5", "Parent" => 1]);
	
	/* INSERT tovar */
	$t1 = Product::insert(["Name" => "Товар 1", "Url" => "товар-1", "Price" => "100,46",  "Category_ID" => $c1['ID']]);
	$t2 = Product::insert(["Name" => "Товар 2", "Url" => "товар-2", "Price" => 204.48,    "Category_ID" => $c1['ID']]);
	$t3 = Product::insert(["Name" => "Товар 3", "Url" => "товар-3", "Price" => "412,12",  "Category_ID" => $c1['ID']]);
	$t4 = Product::insert(["Name" => "Товар 4", "Url" => "товар-4", "Price" => "100",     "Category_ID" => $c2['ID'], "Active" => 0]);
	$t5 = Product::insert(["Name" => "Товар 5", "Url" => "товар-5", "Price" => "200.461", "Content" => "Описание 5", "Category_ID" => $c2['ID'], "Active" => 0]);
	
	/* UPDATE */
	$c2_new = Category::update(["Name" => "Новая категория"], $c2['ID']);
	
	/* DELETE */
	$c5_delete = Category::delete($c5['ID']);
	
	/* Сортировка вверх */
	Product::order($t3['ID'], "up");
	
	/* Проверка по перичному ключу */
	var_dump(Category::is(1, false));		/* bool(true) */
	var_dump(Category::is(5, false));		/* bool(false) */
	
	/* Выборка по первичному ключу */
	print_r(Category::get(3));
	
	/* Выборка корневых категорий. Преобразуется в IS NULL */
	print_r(Category::select(["Parent" => null]));
	
	/* Посмотреть php-тип выборки */
	var_dump(Product::select());
	
	/* Делам SELECT через оператор LIKE */
	print_r(Product::select(["Name" => ["like", "%вар_3%"]]));
}
catch (Exception_Many $e)
{
	print_r($e->get_err());
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>