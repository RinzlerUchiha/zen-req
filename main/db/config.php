<?php
defined("DB_ZEN") or define("DB_ZEN", (getenv('ZEN_DB_DATABASE_PORTAL') ?: ""));
defined("DB_ATD") or define("DB_ATD", (getenv('ZEN_DB_DATABASE_ATD') ?: ""));
defined("DB_HRD") or define("DB_HRD", (getenv('ZEN_DB_DATABASE_HRD2') ?: ""));
defined("DB_MAIN") or define("DB_MAIN", "db_main");
// defined("SESSION_KEY") or define("SESSION_KEY", "DEMOHR_UID");
defined("SESSION_KEY") or define("SESSION_KEY", "user_id");

$DB_ATD = DB_ATD;
$DB_HRD = DB_HRD;
$DB_MAIN = DB_MAIN;
$SESSION_KEY = SESSION_KEY;