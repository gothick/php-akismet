<?php

error_reporting(E_ALL | E_STRICT);
require_once dirname(__DIR__) . '/vendor/autoload.php';

$AKISMET_API_KEY = getenv('AKISMET_API_KEY');
