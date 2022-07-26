<?php
class outletModel{

    public function __construct($name,$email,$contact,$street,$district,$state,$postcode,$taman)
    {
        $this->name=$name;
        $this->email=$email;
        $this->contact=$contact;
        $this->street=$street;
        $this->district=$district;
        $this->state=$state;
        $this->postcode=$postcode;
        $this->taman=$taman;
    }
}
?>