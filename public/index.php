<?php
// php -S localhost:8080 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Kreait\Firebase\Contract\Storage ;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;

require __DIR__ . '/../../vendor/autoload.php';
require_once('../model/user.php');
require_once('../model/service.php');
require_once('../model/why.php');
require_once('../model/department.php');
require_once('../model/outlet.php');
error_reporting(E_ALL ^ E_DEPRECATED);
$app = new \Slim\App;

$registerRoutes=require __DIR__ . '/registration.php';
$registerRoutes($app);
$appointmentRoutes=require __DIR__.'/appointment.php';
$appointmentRoutes($app);
$outletRoutes=require __DIR__ . '/outlet.php';
$outletRoutes($app);
$departmentRoutes=require __DIR__ . '/department.php';
$departmentRoutes($app);
$app->run();












