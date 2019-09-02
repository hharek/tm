<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";

Category::create(true, true);
Product::create(true, true);
?>