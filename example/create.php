<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

Category::tcheck();
Category::create(true);

Product::tcheck();
Product::create(true);
?>