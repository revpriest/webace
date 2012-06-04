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

class Application_Model_Cookie {
    CONST PASSWORD_SALT = "p'oa498jwfoi;jt;49qa8u5t-]fsliv2'-0fe/ikvsdg9se98f";
    protected $_id;
    protected $_nick;
    protected $_email;
    protected $_displaymode;
    protected $_savename;
    protected $_password;
    protected $_twitter;
    protected $_facebook;
    protected $_ccemail;
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
            throw new Exception('Invalid Cookie property');
        }
        $this->$method($value);
    }

    /***************************************************
    * Get a named field
    */ 
    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Cookie property');
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

    public function setId($text) {
        $this->_id = (string)$text;
        return $this;
    }
    public function setNick($text) {
        $this->_nick = (string) $text;
        return $this;
    }
    public function setDisplayMode($dm) {
        $this->_displaymode = (int)$dm;
        return $this;
    }
    public function setSaveName($text) {
        $this->_savename = (string) $text;
        return $this;
    }
    public function setEmail($text) {
        $this->_email = (string) $text;
        return $this;
    }
    public function setPassword($text) {
        $this->_password = (string) $text;
        return $this;
    }
    public function setTwitter($text) {
        $this->_twitter = (string) $text;
        return $this;
    }
    public function setFacebook($text) {
        $this->_facebook = (string) $text;
        return $this;
    }
    public function setCCEmail($text) {
        $this->_ccemail = (string) $text;
        return $this;
    }
    public function setCreated($d){
        $this->_created = $d;
        return $this;
    }
    public function setUpdated($d){
        $this->_updated = $d;
        return $this;
    }


    public function getId() {
        return $this->_id;
    }
    public function getNick() {
        return $this->_nick;
    }
    public function getDisplayMode() {
        return $this->_displaymode;
    }
    public function getDisplayModeName() {
        switch($this->_displaymode){
          case null:
          case 0: return "Single-Page";
          case 1: return "Whole-Domain";
          case 2: return "Whole-Internet";
        }
        return "Unknown";
    }
    public function getSaveName() {
        return $this->_savename;
    }
    public function getEmail() {
        return $this->_email;
    }
    public function getEmailMd5() {
        return md5($this->_email);
    }
    public function getPassword() {
        return $this->_password;
    }
    public function getTwitter() {
        return $this->_twitter;
    }
    public function getFacebook() {
        return $this->_facebook;
    }
    public function getCCEmail() {
        return $this->_ccemail;
    }
    public function getCreated() {
        return $this->_created;
    }
    public function getUpdated() {
        return $this->_updated;
    }


    /***************************************************
    * Find all the nicks this user has ever used. If
    * they have an email address registered, then
    * that includes all the nicks posted under that
    * email address too.
    */
    public function getAllNicks() {
      $commentTable = new Application_Model_CommentMapper();
      $commentTable = $commentTable->getDbTable();
      if($this->getEmail()){
        $orEmail=" or email='".$this->getEmail()."'";
      }else{
        $orEmail="";
      }
      $select=$commentTable->select()->group('nick')->where("cookie='".$this->getId()."'".$orEmail);
      $rows = $commentTable->fetchAll($select);
      $ret = array();
      foreach($rows as $r){
        $ret[]=$r->nick;
      }
      return $ret;
    }


    public static function makeNewCookie(){
      $cookie = new Application_Model_Cookie(array('id'=>null,'ip'=>$_SERVER['REMOTE_ADDR'],'nick'=>"Anon_".self::generateRandomKey(6)));
      $mapper = new Application_Model_CookieMapper();
      $mapper->save($cookie);
      $cookieKey = $cookie->getId();
      setcookie('cookieKey',$cookieKey,time()+(7*24*60*60),"/");
      return $cookie;
    }


    public static function generateRandomKey($size=60,$charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"){
      /*********************************************************
      * Genarate a string of length $size with rnadom alphanums.
      */
      $str = '';
      $count = strlen($charset);
      while ($size--) {
          $str .= $charset[mt_rand(0, $count-1)];
      }
      return $str;
    }

}

