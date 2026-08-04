<?php
defined("DB_ZEN") or define("DB_ZEN", (getenv('ZEN_DB_DATABASE_PORTAL') ?: "portal_db"));
defined("DB_ATD") or define("DB_ATD", (getenv('ZEN_DB_DATABASE_ATD') ?: ""));
defined("DB_HRD") or define("DB_HRD", (getenv('ZEN_DB_DATABASE_HRD2') ?: "tngc_hrd2"));
defined("DB_MAIN") or define("DB_MAIN", "db_main");
defined("DB_EEI") or define("DB_EEI", (getenv('ZEN_DB_DATABASE_EEI') ?: ""));
defined("SESSION_KEY") or define("SESSION_KEY", "user_id");

$DB_ATD = DB_ATD;
$DB_HRD = DB_HRD;
$DB_MAIN = DB_MAIN;
$DB_EEI = DB_EEI;
$SESSION_KEY = SESSION_KEY;
$FILES_DIR = getenv('ZEN_UPLOAD_DIR') ?: '';
$FLIGHTBOOKING_FILES_DIR = getenv('ZEN_UPLOAD_DIR_FLIGHTBOOKING') ?: '';
$FILES_DIR_PCF = getenv('ZEN_UPLOAD_DIR_PCF') ?: '';

date_default_timezone_set('Asia/Manila');
