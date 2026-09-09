<?php
session_start();
require_once '../app/config/database.php';
require_once '../app/core/Pagination.php';
require_once '../app/core/Model.php';
require_once '../app/core/Currency.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Validation.php';
require_once '../app/core/Router.php';

// Apply the configured system timezone to every request BEFORE any date()/
// time()/strtotime() call happens. Previously unset, so PHP fell back to the
// server default (usually UTC), which caused "current time" to render wrong
// throughout invoices, credit notes, pay-ins and pay-outs.
date_default_timezone_set(app_timezone());

$app = new Router();