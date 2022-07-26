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
        "/department",
        function (Request $request, Response $response, $args) {
            $data = $request->getBody();
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dataInJson = json_decode($data);
            $e = json_decode(($dataInJson));
            $department = new departmentModel($e->id,$e->name, $e->mainDescription, $e->secondDescription);

            $dbRef = $Firebase->getReference("department/$department->name");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->set($department);
                $response->getBody()->write("Department exists");
            } else {
                $dbRef->set($department);
                if ($dbRef->getSnapshot()->exists()) {
                    $response->getBody()->write("New department is added");
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
        "/departments",
        function (Request $request, Response $response, $args) {
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dbRef = $Firebase->getReference("department");
            $snapshot=$dbRef->getSnapshot();
            $value=$snapshot->getValue();
            echo json_encode($value);
        }
    );
    $app->get(
        "/department/{departmentname}",
        function (Request $request, Response $response, $args) {
            $departmentname=$args['departmentname'];
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dbRef = $Firebase->getReference("department/$departmentname");
            $value=$dbRef->getValue();
            echo json_encode($value);
        }
    );
    $app->put(
        "/department/{departmentname}",
        function (Request $request, Response $response, $args) {
            $departmentname=$args['departmentname'];
            $id=$request->getParsedBody()['id'];
            $mainDescription=$request->getParsedBody()['mainDescription'];
            $secondDescription=$request->getParsedBody()['secondDescription'];
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();

            $dbRef = $Firebase->getReference("department/$departmentname");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->update(['id'=>$id,'name'=>$departmentname,'mainDescription'=>$mainDescription,'secondDescription'=>$secondDescription]);
                $response->getBody()->write("Outlet details has been updated");
            } 
            return $response;
        }
    );

    $app->delete(
        "/department/{departmentname}",
        function (Request $request, Response $response, $args) {
            $departmentname=$args['departmentname'];
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dbRef = $Firebase->getReference("department/$departmentname");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->remove();
                $response->getBody()->write("Department has been deleted");
            } 
            return $response;
        }
    );
    
};
?>