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
	echo "-- Category --\n";
	Category::check_struct();
	Category::_meta();
	echo Category::_sql_create();
	
	echo "\n\n-- Tovar --\n";
	Tovar::check_struct();
	Tovar::_meta();
	echo Tovar::_sql_create();
	
	echo "\n\n-- Group --\n";
	Group::check_struct();
	Group::_meta();
	echo Group::_sql_create();
	
	echo "\n\n-- User --\n";
	User::check_struct();
	User::_meta();
	echo User::_sql_create();
	
	
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>