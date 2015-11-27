<?php

/**
 * The component class.
 *
 * The base class for Auth, Security classes.
 * It provides reusable controller logic.
 * The extending classes can be used as part of the controller.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
 class Component{

     /**
      * controller
      *
      * @var Controller
      */
     protected $controller;

     /**
      * request
      *
      * @var Request
      */
     protected $request;

     /**
      * Default configurations data
      *
      * @var array
      */
     protected $config = [];


     /**
      * Constructor
      *
      * @param Controller $controller
      * @param array      $config user-provided config
      */
    public function __construct(Controller $controller, array $config = []){
        $this->controller = $controller;
        $this->request    = $controller->request;
        $this->config     = array_merge($this->config, $config);
    }

     /**
      * set and get configurations data
      *
      * @param  string $key
      * @param  mixed  $value
      * @return mixed
      */
     public function config($key, $value = null){

         // set
         if($value !== null){
             $this->config = array_merge($this->config, [$key => $value]);
             return $this;
         }

         // get
         return array_key_exists($key, $this->config)? $this->config[$key]: null;
     }

}
