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
    //Register user
    $app->post(
        "/register",
        function (Request $request, Response $response, $args) {
            $data = $request->getBody();
            $Firebase=dbConnection::connectDb();
            //$dataInJson = json_decode($data);
            $e = json_decode(($data));
            $userr = new userModel($e->email, $e->password);
            $userr->email = str_replace('.', '', $userr->email);
            $dbRef = $Firebase->getReference("user/$userr->email");
            if ($dbRef->getSnapshot()->exists()) {
                $message=new stdClass();
                $message->status=false;
                $message->reason='Email has been used';
                
            } else {
                $dbRef->set($userr);
                if ($dbRef->getSnapshot()->exists()) {
                    $message=new stdClass();
                    $message->status=true;
                    $message->reason="Registration Success";
                } else {
                    $message=[
                        'status'=>false,
                        'message'=>'Unexpected Error In Registration'
                    ];
                }
            }
            $response->getBody()->write(json_encode($message));
            return $response;});

    $app->post(
        "/login",
        function (Request $request, Response $response, $args) {
            $data = $request->getBody();
            $Firebase=dbConnection::connectDb();
            $e = json_decode(($data));
            $userr = new userModel($e->email, $e->password);
            $userr->email = str_replace('.', '', $userr->email);
            $dbRef = $Firebase->getReference("user/$userr->email");
            if ($dbRef->getSnapshot()->exists()) {
                $value=$dbRef->getSnapshot()->getValue();
                if($value['email']==$userr->email and $value['password']==$userr->password)
                {

                    $token=tokenModel::generateToken($e->email);
                    $dbRef = $Firebase->getReference("token/$token->Id");
                    $dbRef->set($token);
                    $message=new stdClass();
                    $message->status=true;
                    $response=$response->withHeader("Authorization",$token->Id);
                    $response->getBody()->write(json_encode($message));
                }else
                {
                    $returnMessage=new stdClass();
                    $returnMessage->status=false;
                    $returnMessage->message='Wrong password or email';
                    $response->getBody()->write(json_encode(($returnMessage)));
                }
            }
            else
            {
                $returnMessage=new stdClass();
                    $returnMessage->status=false;
                    $returnMessage->message='Wrong password or email';
                    $response->getBody()->write(json_encode(($returnMessage)));
            }
            return $response;
        }
    );
    $app->put('/user',function (Request $request, Response $response, $args) {

        $token=$request->getHeader("Authorization");
        $token=$token[0];
        $data = $request->getBody();
        try
        {
            $token=new tokenModel($token);
            if(!$token->isExpired())
            {
            $email=$token->tokenEmail;
            $data=json_decode($data);
            $Firebase=dbConnection::connectDb();
            $dbRef=$Firebase->getReference("user/$email");
            $data->email=$email;
            $dbRef->set($data);
            $returnMessage=
            [
                'status'=>true,
                'message'=>"Password updated"
            ];
            $returnMessage=json_encode($returnMessage);
            }
            else
            {
                $returnMessage=$token->expired();
            }
            

        }
        catch(Exception $e)
        {
            $returnMessage=tokenModel::notFound();
        }
        $response->getBody()->write($returnMessage);
        return $response;
    });
    $app->delete('/user',function (Request $request, Response $response, $args) {
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        $tokenId=$token;
        try
        {
            $token=new tokenModel($token);
            
            if(!$token->isExpired())
            {
            
            $email=$token->tokenEmail;
            $Firebase=dbConnection::connectDb();
            $dbRef=$Firebase->getReference("user");
            $dbRef->removeChildren([$email]);
            $dbRef=$Firebase->getReference(('token'));
            $dbRef->removeChildren([$tokenId]);
            $returnMessage=[
                'status'=>true,
                'message'=>'Account Deleted'
            ];
            $returnMessage=json_encode($returnMessage);
            }
            else
            {
                $returnMessage=$token->expired();
            }
        }
        catch(Exception $e)
        {
            $returnMessage=tokenModel::notFound();
            
            
        }
        $response->getBody()->write($returnMessage);
        return $response;
    });
    $app->get('/token',function (Request $request, Response $response, $args) {
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        try
        {
            $token=new tokenModel($token);

            if(!$token->isExpired())
            {
                $returnMessage=[
                    'status'=>true,
                ];
                $returnMessage=json_encode($returnMessage);
            }
            else
            {
                $returnMessage=$token->expired();
            }
        }
        catch(Exception $e)
        {
            $returnMessage=tokenModel::notFound();

        }
        $response->getBody()->write($returnMessage);
        return $response;
    });
    $app->post('/admin',function (Request $request, Response $response, $args) {
        $data = $request->getBody();
        $Firebase=dbConnection::connectDb();
        $e = json_decode(($data));
        $e->email=str_replace('.', '', $e->email);
        $dbRef = $Firebase->getReference("admin/$e->email");
            if ($dbRef->getSnapshot()->exists()) {
                $value=$dbRef->getSnapshot()->getValue();
                if($value['email']==$e->email and $value['password']==$e->password)
                {

                    $token=tokenModel::generateToken($e->email);
                    $dbRef = $Firebase->getReference("token/$token->Id");
                    $dbRef->set($token);
                    $message=new stdClass();
                    $message->status=true;
                    $response=$response->withHeader("Authorization",$token->Id);
                    $response->getBody()->write(json_encode($message));
                }else
                {
                    $returnMessage=new stdClass();
                    $returnMessage->status=false;
                    $returnMessage->message='Wrong password or email';
                    $response->getBody()->write(json_encode(($returnMessage)));
                }
            }
            else
            {
                $returnMessage=new stdClass();
                    $returnMessage->status=false;
                    $returnMessage->message='Wrong password or email';
                    $response->getBody()->write(json_encode(($returnMessage)));
            }
            return $response;
    });
    //Register user
    $app->post(
        "/service",
        function (Request $request, Response $response, $args) {
            $data = $request->getBody();
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dataInJson = json_decode($data);
            $e = json_decode(($dataInJson));
            $service = new serviceModel($e->name, $e->description, $e->icon);

            $dbRef = $Firebase->getReference("service/$service->name");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->set($service);
                $response->getBody()->write("service has been used");
            } else {
                $dbRef->set($service);
                if ($dbRef->getSnapshot()->exists()) {
                    $response->getBody()->write("Registration Success");
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
        //Register user
        $app->post(
            "/serviceUpdate",
            function (Request $request, Response $response, $args) {
                $data = $request->getBody();
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dataInJson = json_decode($data);
                $e = json_decode(($dataInJson));
                $service = new serviceModel($e->name, $e->description, $e->icon);
    
                $dbRef = $Firebase->getReference("service/$e->name");
                if ($dbRef->getSnapshot()->exists()) {
                    $dbRef->set($service);
                    $response->getBody()->write("service has been used");
                } else {
                    $dbRef->set($service);
                    if ($dbRef->getSnapshot()->exists()) {
                        $response->getBody()->write("Registration Success");
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
            "/service/{service}",
            function (Request $request, Response $response, $args) {
                $departmentname=$args['service'];
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dbRef = $Firebase->getReference("service/$departmentname");
                $value=$dbRef->getValue();
                echo json_encode($value);
            }
        );
        $app->put(
            "/service/{service}",
            function (Request $request, Response $response, $args) {
                $departmentname=$args['service'];
                $description=$request->getParsedBody()['description'];
                $icon=$request->getParsedBody()['icon'];
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
    
                $dbRef = $Firebase->getReference("service/$departmentname");
                if ($dbRef->getSnapshot()->exists()) {
                    $dbRef->update(['name'=>$departmentname,'description'=>$description,'icon'=>$icon]);
                    $response->getBody()->write("Outlet details has been updated");
                } 
                return $response;
            }
        );
        $app->get(
            "/service",
            function (Request $request, Response $response, $args) {
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dbRef = $Firebase->getReference("service");
                $snapshot=$dbRef->getSnapshot();
                $value=$snapshot->getValue();
                echo json_encode($value);
            }
        );
        $app->delete(
            "/service/{service}",
            function (Request $request, Response $response, $args) {
                $departmentname=$args['service'];
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dbRef = $Firebase->getReference("service/$departmentname");
                if ($dbRef->getSnapshot()->exists()) {
                    $dbRef->remove();
                    $response->getBody()->write("Service has been deleted");
                } 
                return $response;
            }
        );
            //why
    $app->post(
        "/whyMedilab",
        function (Request $request, Response $response, $args) {
            $data = $request->getBody();
            $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
            $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
            $dataInJson = json_decode($data);
            $e = json_decode(($dataInJson));
            $why = new whyModel($e->order,$e->header, $e->description, $e->icon);

            $dbRef = $Firebase->getReference("whyMedilab/$why->header");
            if ($dbRef->getSnapshot()->exists()) {
                $dbRef->set($why);
                $response->getBody()->write("service has been used");
            } else {
                $dbRef->set($why);
                if ($dbRef->getSnapshot()->exists()) {
                    $response->getBody()->write("Registration Success");
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
        //Register user
        $app->post(
            "/whyMedilabUpdate",
            function (Request $request, Response $response, $args) {
                $data = $request->getBody();
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dataInJson = json_decode($data);
                $e = json_decode(($dataInJson));
                $why = new whyModel($e->order,$e->header, $e->description, $e->icon);
    
                $dbRef = $Firebase->getReference("whyMedilab/$e->key");
                if ($dbRef->getSnapshot()->exists()) {
                    $dbRef->set($why);
                    $response->getBody()->write("service has been used");
                } else {
                    $dbRef->set($why);
                    if ($dbRef->getSnapshot()->exists()) {
                        $response->getBody()->write("Registration Success");
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
            "/whyMedilab/{whyMedilab}",
            function (Request $request, Response $response, $args) {
                $departmentname=$args['whyMedilab'];
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dbRef = $Firebase->getReference("whyMedilab/$departmentname");
                $value=$dbRef->getValue();
                echo json_encode($value);
            }
        );
        $app->put(
            "/whyMedilab/{whyMedilab}",
            function (Request $request, Response $response, $args) {
                $header=$args['whyMedilab'];
                $description=$request->getParsedBody()['description'];
                $order=$request->getParsedBody()['order'];
                $icon=$request->getParsedBody()['icon'];
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
    
                $dbRef = $Firebase->getReference("whyMedilab/$header");
                if ($dbRef->getSnapshot()->exists()) {
                    $dbRef->update(['description'=>$description,'header'=>$header,'icon'=>$icon,'order'=>$order]);
                    $response->getBody()->write("Outlet details has been updated");
                } 
                return $response;
            }
        );
        $app->get(
            "/whyMedilab",
            function (Request $request, Response $response, $args) {
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dbRef = $Firebase->getReference("whyMedilab");
                $snapshot=$dbRef->getSnapshot();
                $value=$snapshot->getValue();
                echo json_encode($value);
            }
        );
        $app->delete(
            "/whyMedilab/{whyMedilab}",
            function (Request $request, Response $response, $args) {
                $header=$args['whyMedilab'];
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
                $dbRef = $Firebase->getReference("whyMedilab/$header");
                if ($dbRef->getSnapshot()->exists()) {
                    $dbRef->remove();
                    $response->getBody()->write("Why has been deleted");
                } 
                return $response;
            }
        );
};
?>
