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

class Application_Model_Urlcache {
    CONST HOTNESS_INITIAL = 5000;
    CONST HOTNESS_COOLRATE = 0.0001;
    CONST HOTNESS_BOOST = 2500;

    protected $_id;
    protected $_domain;
    protected $_path;
    protected $_title;
    protected $_postcount;
    protected $_hotness;
    protected $_hottime;
    protected $_created;
    protected $_updated;


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
            throw new Exception('Invalid UrlCache property');
        }
        $this->$method($value);
    }

    /***************************************************
    * Get a named field
    */ 
    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid UrlCache property');
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

    public function setId($id){
        $this->_id = (int) $id;
        return $this;
    }
    public function setDomain($text) {
        $this->_domain = (string) $text;
        return $this;
    }
    public function setPath($text) {
        $this->_path = (string) $text;
        return $this;
    }
    public function setTitle($text) {
        $this->_title = (string) $text;
        return $this;
    }
    public function setPostCount($i) {
        $this->_postcount = (int) $i;
        return $this;
    }
    public function setHotness($i) {
        $this->_hotness = (int) $i;
        return $this;
    }
    public function setHotTime($i) {
        $this->_hottime = (int) $i;
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


    public function getId() {
        return $this->_id;
    }
    public function getDomain() {
        return $this->_domain;
    }
    public function getPath() {
        return $this->_path;
    }
    public function getTitle() {
        return $this->_title;
    }
    public function getPostCount() {
        return $this->_postcount;
    }
    public function getHotness() {
        return $this->_hotness;
    }
    public function getCurrentHotness() {
        return $this->_hotness * exp(-self::HOTNESS_COOLRATE * (time()-$this->_hottime));
    }
    public function getHotTime() {
        return $this->_hottime;
    }
    public function getCreated() {
        return $this->_created;
    }
    public function getUpdated() {
        return $this->_updated;
    }

    public function incPostcount(){
      /* Increment the post-count and boost the hotness */
      $this->_hotness = $this->getCurrentHotness()+self::HOTNESS_BOOST;
      $this->_hottime = time();
      $this->_postcount++;
    }
}

