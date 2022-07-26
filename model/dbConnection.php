<?php 
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Factory;
class dbConnection
{
    public static function connectDb()
    {
        try{
        $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
        $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
        return $Firebase;
        }
        catch(Exception $e)
        {
            throw new Exception("Fail to connect to database", 1);  
        }
        
    }
}