<?php

 class CheckBox{
        var $id;
        var $value;
      
      /* Member functions */
      function setId($id){
         $this->id = $id;
      }
      
      function getId(){
         return $this->id; 
      }
      
      function setValue($value){
         $this->value = $value;
      }
      
      function getValue(){
         return $this->value;  
      }
    }
        
    
    class Menus{
        var $id;
        var $value;
      
      /* Member functions */
      function setId($id){
         $this->id = $id;
      }
      
      function getId(){
         return $this->id; 
      }
      
      function setValue($value){
         $this->value = $value;
      }
      
      function getValue(){
         return $this->value;  
      }
    }
    
    class DateTimee{
        var $id;
        var $startDate;
        var $endDate;
      
      /* Member functions */
      function setId($id){
         $this->id = $id;
      }
      
      function getId(){
         return $this->id; 
      }
      
      function setStartDate($startDate){
         $this->startDate = $startDate;
      }
      function getStartDate(){
         return $this->startDate;
      }
    
      function setEndDate($endDate){
         $this->endDate = $endDate;
      }
      
      function getEndDate(){
         return $this->endDate;  
      }
    }
   