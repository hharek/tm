<?php
header("Content-type: text/plain; charset=UTF-8");

require "../tm.php";
require "../tm_ftype.php";

require "table/category.php";
require "table/tovar.php";
require "table/group.php";
require "table/user.php";

$db = pg_connect("host=127.0.0.1 port=5432 dbname=example user=example password=pass");
TM::set_db_conn($db);

try
{
	/* Проверяем правильность проверки заполнения класса Category */
	Category::check_struct();
	
	/* Создаём таблицу */
	Category::create();
	
	/* INSERT */
	$c1 = Category::insert(["Name" => "Категория 1", "Url" => "категория-1", "Parent" => null]);
	$c2 = Category::insert(["Name" => "Категория 2", "Url" => "категория-2", "Parent" => null]);
	$c3 = Category::insert(["Name" => "Категория 3", "Url" => "категория-3", "Parent" => null]);
	$c4 = Category::insert(["Name" => "Категория 4", "Url" => "категория-4", "Parent" => 1]);
	$c5 = Category::insert(["Name" => "Категория 5", "Url" => "категория-5", "Parent" => 1]);
	
	/* UPDATE */
	$c2_new = Category::update(["Name" => "Новая категория"], $c2['ID']);
	
	/* DELETE */
	$c5_delete = Category::delete($c5['ID']);
	
	/* Проверка по перичному ключу */
	var_dump(Category::is(1, false));		/* bool(true) */
	var_dump(Category::is(5, false));		/* bool(false) */
	
	/* Выборка по первичному ключу */
	print_r(Category::get(3));
	
	/* Выборка */
	print_r(Category::select(["Parent" => null]));	/* Выборка корневых категорий */
	
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