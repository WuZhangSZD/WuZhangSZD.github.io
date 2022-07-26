<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Kreait\Firebase\Contract\Storage ;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;
use Slim\App;
include ('../model/appointmentModel.php');
require_once('../model/token.php');
require_once('../model/dbConnection.php');
return function(App $app)
{

    $app->post('/appointment',
    function (Request $request, Response $response, $args) {
        $data = $request->getBody();
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        try{
            $token=new tokenModel($token);
            if($token->isExpired())
            {
                $returnMessage = [
                    'status' => false,
                    'reason' => 'token expired',
                ];
                $response->getBody()->write(json_encode($returnMessage));
            }
            else
            {
                $data = json_decode($data);
                $Firebase=dbConnection::connectDb();
                $apt=new appointmentModel($token->email,$data->name,$data->phone,$data->date,$data->message);
                $idEmail=str_replace('.', '', $token->email);
                $dbRef = $Firebase->getReference("appointment/$idEmail");
                $dbRef->push($apt);
            }
        }catch(Exception $e)
        {

        }
    });


    $app->get('/appointment',function (Request $request, Response $response, $args) {
    $token=$request->getHeader("Authorization");
    $token=$token[0];
    try{
        $token=new tokenModel($token);
        if($token->isExpired())
        {
            $returnMessage = [
                'status' => false,
                'reason' => 'token expired',
            ];
            $response->getBody()->write(json_encode($returnMessage));
        }
        else
        {
            $email=$token->tokenEmail;
            try {
                $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
                $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
    
                $dbRef = $Firebase->getReference("appointment/$email");
                if($dbRef->getSnapshot()->exists())
                {
                    $appointmentList = $dbRef->getSnapshot()->getValue();
                $returnMessage = [
                    'status' => true,
                    'size' => sizeof($appointmentList),
                    'appointment' => $appointmentList
                ];
                $response->getBody()->write(json_encode($returnMessage));
                }
                else
                {
                    $returnMessage=[
                        'status'=>true,
                        'size'=>0,
                    ];
                    $response->getBody()->write(json_encode($returnMessage));
                }
                

                
            } catch (Exception $e) {
                
            }
        }
    }
    catch(Exception $e)
    {
        $returnMessage = [
            'status' => false,
            'reason' => 'token not found'
        ];
        $response->getBody()->write(json_encode($returnMessage));
    }
    return $response;
    });


    $app->delete('/appointment/{id}',function (Request $request, Response $response, $args) {
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        $id=$args['id'];
        try
        {
            $token=new tokenModel($token);
            if(!$token->isExpired())
            {
                $email=$token->tokenEmail;
                $Firebase=dbConnection::connectDb();
                $dbRef=$Firebase->getReference("appointment/$email");
                $dbRef->removeChildren([$id]);
                $returnMessage=[
                    'status'=>true,
                    'message'=>'Appointment Deleted'
                ];
                
            }
            else
            {
                $returnMessage=$token->expired();
            }
        }
        catch(Exception $e)
        {
            $returnMessage=$token->notFound();
        }
        $response->getBody()->write(json_encode($returnMessage));
        return $response;        
    });


    $app->get('/appointment/{id}',function (Request $request, Response $response, $args) {
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        $id=$args['id'];
        try{
            $token=new tokenModel($token);
            if(!$token->isExpired())
            {
                $Firebase=dbConnection::connectDb();
                $dbRef=$Firebase->getReference("appointment/$token->tokenEmail/$id");
                $appointment=$dbRef->getSnapshot()->getValue();
                $returnMessage=json_encode($appointment);
            }
            
        }
        catch(Exception $e)
        {

        }
        $response->getBody()->write($returnMessage);
        return $response;
        
    });

    $app->put('/appointment/{id}',function (Request $request, Response $response, $args) {
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        $id=$args['id'];
        $data = $request->getBody();
        try{
            $token=new tokenModel($token);
            
            if(!$token->isExpired())
            {
                $data=json_decode($data);
                $data->email=$token->email;
                $Firebase=dbConnection::connectDb();
                $dbRef = $Firebase->getReference("appointment/$token->tokenEmail/$id");
                $dbRef->set($data);
            }
        }
        catch(Exception $e)
        {
            
        }

    });


    $app->get('/appointment/admin/all',function (Request $request, Response $response, $args) {
        $token=$request->getHeader("Authorization");
        $token=$token[0];
        $Firebase=dbConnection::connectDb();

        $dbRef=$Firebase->getReference("appointment");
        $appointmentList=$dbRef->getSnapshot()->getValue();
                
                $returnMessage=json_encode($appointmentList);
                $response->getBody()->write($returnMessage);
                return $response;
        try
        {
            $token=new tokenModel($token);
            $email=$token->tokenEmail;
            $Firebase=dbConnection::connectDb();
            $dbRef=$Firebase->getReference("admin/$email");
            if($dbRef->getSnapshot()->exists())
            {
                $dbRef=$Firebase->getReference("appointment");
                $appointmentList=$dbRef->getSnapshot()->getValue();
                $returnMessage=json_encode($appointmentList);
            }


        }
        catch(Exception $e)
        {

        }

    });
    
};
?>