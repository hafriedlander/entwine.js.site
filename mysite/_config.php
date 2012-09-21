<?php

global $project;
$project = 'mysite';

global $database;
$database = 'SS_entwine_js';

require_once('conf/ConfigureFromEnv.php');

MySQLDatabase::set_connection_charset('utf8');

// This line set's the current theme. More themes can be
// downloaded from http://www.silverstripe.org/themes/
SSViewer::set_theme('entwine');
