<?php
/********************************************************
* The actual model class. Coming from Symfony it's weird
* to me that I had to actually create the content of this
* file by hand. What's the point of the "zf create model"
* command if all it makes it an almost empty file? Surely
* it should examine the database, find the details of
* the table and automatically build this entire class
* shouldn't it? Apparently Zend doesn't. Probably that's
* a "Doctrine" thing I expect. Maybe if I was using
* Doctrine here it'd have done a better job. Mostly
* it's pasted form the Tutorial and edited.
*/

class Application_Model_Comment {
    protected $_domain;
    protected $_path;
    protected $_cookie;
    protected $_nick;
    protected $_email;
    protected $_ip;
    protected $_content;
    protected $_reply_to;
    protected $_created;
    protected $_updated;
    protected $_id;


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
            throw new Exception('Invalid comment property');
        }
        $this->$method($value);
    }

    /***************************************************
    * Get a named field
    */ 
    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid comment property');
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

    public function setDomain($text) {
        $this->_domain = (string) $text;
        return $this;
    }

    public function setPath($text) {
        $this->_path = (string) $text;
        return $this;
    }

    public function setCookie($text) {
        $this->_cookie = (string) $text;
        return $this;
    }

    public function setNick($text) {
        $this->_nick = (string) $text;
        return $this;
    }

    public function setEmail($text) {
        $this->_email = (string) $text;
        return $this;
    }

    public function setIp($text) {
        $this->_ip = (string) $text;
        return $this;
    }

    public function setContent($text) {
        $this->_content = (string) $text;
        return $this;
    }

    public function setReplyTo($i){
        $this->_reply_to = (int) $i;
        return $this;
    }

    public function setCreated($d){
        $this->_created = $d;
        return $this;
    }

    public function setUpdated($d){
        $this->_created = $d;
        return $this;
    }

    public function setId($id){
        $this->_id = (int) $id;
        return $this;
    }
 
    public function getDomain() {
        return $this->_domain;
    }
    public function getPath() {
        return $this->_path;
    }
    public function getCookie() {
        return $this->_cookie;
    }
    public function getCookieObject() {
        $mapper = new Application_Model_CookieMapper();
        return $mapper->find($this->_cookie);
    }
    public function getNick() {
        return $this->_nick;
    }
    public function getEmail() {
        return $this->_email;
    }
    public function getIp() {
        return $this->_ip;
    }
    public function getContent() {
        return $this->_content;
    }
    public function getReplyTo() {
        return $this->_reply_to;
    }
    public function getCreated() {
        return $this->_created;
    }
    public function getUpdated() {
        return $this->_updated;
    }
    public function getId() {
        return $this->_id;
    }


}

