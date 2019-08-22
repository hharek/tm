<?php
namespace TM;

const PREPARE_JSON_ENCODE = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
const PHP_TYPES = ["string", "int", "integer", "float", "double", "real", "bool", "boolean", "array", "object"];
const PGSQL_BOOLEAN_TRUE = ["true", "yes", "on", "1"];
const PGSQL_BOOLEAN_FALSE = ["false", "no", "off", "0"];

?>