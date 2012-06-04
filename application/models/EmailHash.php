<?php

class Application_Model_EmailHash
{
  protected $_id;
  protected $_email;
  protected $_hash;
  protected $_cookie;
  protected $_created;
  protected $_cookieObj=null;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
	#Generate new ones with random hash built it as standard sir, for your safety!
	$this->_hash = Application_Model_Cookie::generateRandomKey();
    }
 
    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid emailhash property');
        }
        $this->$method($value);
    }
 
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid emailhash property');
        }
        return $this->$method();
    }
 
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
 
    public function setId($id)
    {
        $this->_id = (int) $id;
        return $this;
    }
 
    public function getId()
    {
        return $this->_id;
    }
 
    public function setEmail($email) {
        $this->_email = (string) $email;
        return $this;
    }
 
    public function getEmail() {
        return $this->_email;
    }
 
    public function setHash($hash) {
        $this->_hash = (string) $hash;
        return $this;
    }
 
    public function getHash() {
        return $this->_hash;
    }
 
    public function setCookie($cookie) {
        $this->_cookie = (string) $cookie;
        return $this;
    }
 
    public function getCookie() {
        return $this->_cookie;
    }
    public function fetchCookieObject() {
        if($this->_cookieObj!=null){
          return $this->_cookieObj;
        }else{
          $mapper = new Application_Model_CookieMapper();
          $this->_cookieObj=$mapper->find($this->_cookie);
          return $this->_cookieObj;
        }
    }
 
    public function setCreated($ts)
    {
        $this->_created = $ts;
        return $this;
    }
 
    public function getCreated()
    {
        return $this->_created;
    }
 


}

