<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Kreait\Firebase\Contract\Storage ;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;

$app->post('/',function(Request $request,Response $response,$args)
{
    $data = $request->getBody();
    $serviceAccount=ServiceAccount::fromValue(__DIR__.'/../key.json');
    $Firebase=(new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://fir-testing-6d2f3-default-rtdb.firebaseio.com/')->createDatabase();
    $dataInJson=json_decode($data);
    $e=json_decode(($dataInJson));
    $userr=new userModel($e->email,$e->password);
    $dbRef=$Firebase->getReference("user/$userr->email");
    if($dbRef->getSnapshot()->exists())
    {
        $value=$dbRef->getSnapshot()->getValue();
        if($value->email==$userr->email && $value->password==$userr->password)
        {
            $response->getBody()->write("Success");
        }
    }
    $response->getBody()->write("Success");
    return $response;
}
);

?>