<?php
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Factory;
//1=token not found
//2=token not found
class tokenModel
{
    //create token object if token id found
    //throw exception if not found;
    public static function generateToken($email)
    {
        $token=new stdClass();
        $token->Id = sha1(mt_rand(1, 90000) . 'SALT');
        $token->email=$email;
        $token->tokenEmail=str_replace('.', '', $email);
        $token->tokenExpired=new DateTime();
        $token->tokenExpired->add(DateInterval::createFromDateString('3 hour'));
        $token->tokenExpired=$token->tokenExpired->format('Y-m-d H:i:s');
        return $token;
    }
    public function __construct($token)
    {
    try{
        $serviceAccount = ServiceAccount::fromValue(__DIR__ . '/../key.json');
        $Firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri
        ('https://webtech-ddcf8-default-rtdb.asia-southeast1.firebasedatabase.app/')->createDatabase();
        $dbRef = $Firebase->getReference("token/$token");
        
        if ($dbRef->getSnapshot()->exists()) {
        $tokenSnapshot = $dbRef->getSnapshot()->getValue();
        $this->tokenEmail=$tokenSnapshot['tokenEmail'];
        $this->email = $tokenSnapshot['email'];
        $this->expiredToken = $tokenSnapshot['tokenExpired'];
        }   
        else
        {
        throw new Exception("Token not found",1);   
        }
    }
    catch(Exception $e)
    {
        throw new Exception($e);
    }
    }
    public function isExpired()
    {
        $dateTimeNow = new DateTime();
        $dateTimeNow=$dateTimeNow->format('Y-m-d H:i:s');
        if ($this->expiredToken < $dateTimeNow) {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function expired()
    {
        $returnMessage=
        [
            'status' => false,
            'reason' => 'Session expired',
        ];
        return json_encode($returnMessage);
    }
    public static function notFound()
    {
        $returnMessage = [
            'status' => false,
            'reason' => 'token not found'
        ];
        return json_encode($returnMessage);
    }

}
?>