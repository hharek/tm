<?php
header("Content-type: text/plain; charset=UTF-8");

require "../tm.php";
require "../tm_ftype.php";

require "table/category.php";
require "table/tovar.php";
require "table/group.php";
require "table/user.php";

TM::set_db_conn(pg_connect("host=127.0.0.1 port=5432 dbname=odin user=odin password=111"));


try
{
	var_dump(Category::is(1, false));
	print_r(Category::get(1));
	print_r(Category::select());
	print_r(Category::select(["Name" => "Категория 1"]));
	print_r(Category::select([], ["ID", "Name"]));	
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>