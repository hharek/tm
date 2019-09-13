<?php
namespace TM;

const PREPARE_JSON_ENCODE = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT; 											/* Параметры применяемые в функции json_encode в Column::prepare() */
const PHP_TYPES = ["string", "int", "integer", "float", "double", "real", "bool", "boolean", "array", "object"];	/* Доступные PHP типы */
const PGSQL_BOOLEAN_TRUE = ["true", "yes", "on", "1"];																/* Строка интерпретируемая как true для булёвого типа  */
const PGSQL_BOOLEAN_FALSE = ["false", "no", "off", "0"];															/* Строка интерпретируемая как false для булёвого типа */
const EQUAL_ALLOW = ["=", "like", "ilike"];																			/* Допустимые значения для оператора сравнения */
const DEBUG_TYPE = ["default", "without_value", "prepare", "json"];													/* Отладка. Допустимые типы отображения запроса */
const SQL_COMMENT_SEPARATOR = " |TM| ";																				/* Разделитель данных и служебных данных в SQL комментариях */
const PGSQL_JSON_VERIFY_TYPE = "array";																				/* Если тип столбца json|jsonb каким будет его PHP-тип: array|object */
?>