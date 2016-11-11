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
	
	$data = 
	[
		"Name" => "Категория \n ещё одна",
		"Url" => "кате < гория-1 >"
	];
	
	
	Category::check($data);			
/*
Exception_Many: 
Array
(
    [Name] => Поле «Наименование» задано неверно. Недопустимые символы.
    [Url] => Поле «Урл» задано неверно. Допускаются символы: 0-9,a-z,а-я,«_»,«-»,«.» .
) 
*/
	
	Category::check($data, false);					/* Exception: Поле «Наименование» задано неверно. Недопустимые символы. */

	Category::is("11a11");							/* Exception: Поле «Порядковый номер» задано неверно. Не является числом. */
	
	var_dump(Category::is("11a11", false));			/* bool(false) */
	
	$err = Category::unique(["Name" => "Категория 1"]);	/* Return Array Error */
	
	Category::unique(["Name" => "Категория 1"], null, true);	/* «Категория» с полем «Наименование» : «Категория 1» уже существует. */
	
	Category::unique(["Name" => "Категория 1"], 1);		/* Ошибок нет */
	
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