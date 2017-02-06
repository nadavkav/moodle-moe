<?php
abstract class  moeReport{
    
   abstract function  runreport(); 
   abstract function  displayreportfortemplates();
   
   public function to_std():\stdClass {
       $obj = new \stdClass();
       $vars = get_object_vars($this);
       foreach ($vars as $key => $value) {
           $obj->{$key} = $value;
       }
       return $obj;
   }
    
}

