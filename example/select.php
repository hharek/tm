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
	/* Выборка по первичному ключу */
	$category = Category::get(3);
	print_r($category);
	
	/* Все категории */
	$category_all = Category::select();
	print_r($category_all);
	
	/* Корневые категории */
	$category_parent = Category::select(["Parent" => null]);
	print_r($category_parent);
	
	/* Имена всех подчитённых категорий с 1 */
	$category_child_by_1_names = Category::select(["Parent" => 1], ["Name"]);
	print_r($category_child_by_1_names);
	
	/* Вторая страница категорий, отсортированная по имени */
	$category_by_page_2 = Category::select([], [], 2, 10, ["Name" => "asc"]);
	print_r($category_by_page_2);
	
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