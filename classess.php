<?php
class Fruits{
public $name= "This Fruit Name is pine Apple";
public function GetName(){
    return "This Is a Apple";
}
}
$apple = new Fruits();
echo $apple->name.'<br>';
echo $apple->GetName().'<br>';


class Car{
    public $name= "This is a Car Name ";
    public function GetName(){
        return $this->name;
    }
    }
    $apple = new Car();
    echo $apple->name.'<br>';
    echo $apple->GetName();
?>