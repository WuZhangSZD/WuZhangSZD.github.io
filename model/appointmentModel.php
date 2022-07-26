<?php
class appointmentModel{

    public function __construct($email,$name,$phone,$date,$message)
    {
        $this->email=$email;
        $this->name=$name;
        $this->phone=$phone;
        $this->date=$date;
        $this->message=$message;
    }
}
?>