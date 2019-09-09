<?php
require __DIR__ . "/../autoloader.php";
require "settings.php";
require "table/category.php";
require "table/product.php";


echo "Отладка 1:\n";
Category::debug(true);
Category::create(true);

echo "\n\nОтладка 2:\n";
Category::debug(true, "without_value");
Category::create(true);

echo "\n\nОтладка 3:\n";
Category::debug(true, "prepare");
Category::create(true);

echo "\n\nОтладка 4:\n";
Category::debug(true, "json");
Category::create(true);

Category::debug(true, "default", "/tmp/db.log");
Category::create();

Category::debug(false);
?>