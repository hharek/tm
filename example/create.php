<?php
require "settings.php";
require "../autoloader.php";
require "table/category.php";

Category::tcheck();
Category::create(true);
?>