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
	/* Выборка по первичному ключу */
	$category = Category::get(3);
	print_r($category);
	
	/* Все категории */
	$category_all = Category::select();
	print_r($category_all);
	
	/* Корневые категории. Используем в условии null */
	$category_parent = Category::select(["Parent" => null]);
	print_r($category_parent);
	
	/* Имена всех подчитённых категорий с 1 */
	$category_child_by_1_names = Category::select(["Parent" => 1], ["Name"]);
	print_r($category_child_by_1_names);
	
	/* Вторая страница категорий, отсортированная по имени */
	$category_by_page_2 = Category::select([], [], 2, 10, ["Name" => "asc"]);
	print_r($category_by_page_2);
	
	/* Сделать запрос используя другие операторы в SELECT WHERE. */
	print_r(Product::select(["ID" => [">=", 3]]));
	print_r(Product::select(["ID" => ["in", [1,2,4]]]));
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