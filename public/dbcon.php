<?php
require __DIR__ . '/../../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Kreait\Firebase\Contract\Storage ;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;
use Slim\App;
$serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');

$factory=(new Factory)
->withServiceAccount($serviceAccount)
->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/');
$database=$factory->createDatabase();

?>
