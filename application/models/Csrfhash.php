<?php

class Application_Model_Csrfhash
{
    protected $_cookie;
    protected $_csrf;
    protected $_created;

    /**********************************************************
    * The constructor sets all the options if there are any
    */ 
    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /*******************************************************
    * Setter, sets a named field.
    */ 
    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Csrhash property');
        }
        $this->$method($value);
    }

    /***************************************************
    * Get a named field
    */ 
    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Csrfhash property');
        }
        return $this->$method();
    }

    /***************************************************
    * set a whole arrray full of fields.
    */ 
    public function setOptions(array $options){
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }




    /***************************************************
    * Finally, a bunch of actual getters and setters   *
    * for the individual fields.                       *
    ***************************************************/

    public function setCookie($text) {
        $this->_cookie = (string)$text;
        return $this;
    }
    public function setCsrf($text) {
        $this->_csrf = (string)$text;
        return $this;
    }
    public function setCreated($d){
        $this->_created = $d;
        return $this;
    }


    public function getCookie() {
        return $this->_cookie;
    }
    public function getCsrf() {
        return $this->_csrf;
    }
    public function getCreated() {
        return $this->_created;
    }

}

