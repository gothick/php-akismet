<?php

error_reporting(E_ALL);
require_once dirname(__DIR__) . '/vendor/autoload.php';

global $AKISMET_API_KEY;
$AKISMET_API_KEY = getenv('AKISMET_API_KEY');
