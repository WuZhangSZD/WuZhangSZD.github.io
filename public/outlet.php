<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Kreait\Firebase\Contract\Storage ;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;
use Slim\App;

return function(App $app)
{
    $app->post(
        "/outlet",
        function (Request $request, Response $response, $args) {
            $data = $request->getBody();
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dataInJson = json_decode($data);
            $e = json_decode(($dataInJson));
            $outlet = new outletModel($e->name, $e->email, $e->contact, $e->street, $e->district, $e->state, $e->postcode, $e->taman);

            $dbRef = $Firebase->getReference("outlet/$outlet->name");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->set($outlet);
                $response->getBody()->write("Outlet exists");
            } else {
                $dbRef->set($outlet);
                if ($dbRef->getSnapshot()->exists()) {
                    $response->getBody()->write("New outlet is added");
                } else {
                    $response->getBody()->write(
                        [
                            "status" => 1
                        ]
                    );
                }
            }
            return $response;
        }
    );
    $app->get(
        "/outlets",
        function (Request $request, Response $response, $args) {
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dbRef = $Firebase->getReference("outlet");
            $snapshot=$dbRef->getSnapshot();
            $value=$snapshot->getValue();
            echo json_encode($value);
        }
    );
    $app->get(
        "/outlet/{outletname}",
        function (Request $request, Response $response, $args) {
            $outletname=$args['outletname'];
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dbRef = $Firebase->getReference("outlet/$outletname");
            $value=$dbRef->getValue();
            echo json_encode($value);
        }
    );
    $app->put(
        "/outlet/{outletname}",
        function (Request $request, Response $response, $args) {
            $outletname=$args['outletname'];
            $email=$request->getParsedBody()['Email'];
            $contact=$request->getParsedBody()['Contact'];
            $street=$request->getParsedBody()['Street'];
            $district=$request->getParsedBody()['District'];
            $state=$request->getParsedBody()['State'];
            $postcode=$request->getParsedBody()['Postcode'];
            $taman=$request->getParsedBody()['Taman'];
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();

            $dbRef = $Firebase->getReference("outlet/$outletname");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->update(['name'=>$outletname,'email'=>$email,'contact'=>$contact,'street'=>$street,'district'=>$district,
                'state'=>$state,'postcode'=>$postcode,'taman'=>$taman]);
                $response->getBody()->write("Outlet details has been updated");
            } 
            return $response;
        }
    );
    $app->delete(
        "/outlet/{outletname}",
        function (Request $request, Response $response, $args) {
            $outletname=$args['outletname'];
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dbRef = $Firebase->getReference("outlet/$outletname");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->remove();
                $response->getBody()->write("Outlet details has been deleted");
            } 
            return $response;
        }
    );
};
?>