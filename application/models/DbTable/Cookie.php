<?php

class Application_Model_DbTable_Cookie extends Zend_Db_Table_Abstract
{
    protected $_name = 'cookie';

    public static function getUserCookie($passedCookie=null){
      /******************************************************************
      * Get the cookie object for the current user. If they
      * didn't send us a cookie then we make a new one for 'em.
      * If they did we resurect the one in the DB. Either way
      * we re-set the cookie to last another week!
      */
      $cookieKey=null;
      if(isset($_COOKIE['cookieKey'])){
        $cookieKey = $_COOKIE['cookieKey'];
      }
      if($passedCookie!=null){
	$cookieKey=$passedCookie;
      }
      if((is_string($cookieKey))&&(strlen($cookieKey)>30)){
        //Look up a hopefully existing cookie.
        $cookie = new Application_Model_CookieMapper();
        $cookie=$cookie->find($cookieKey);
        if($cookie!=null){
           return $cookie;
        }
      }
      //For one reason or another we can't trace their
      //Cookie. Best make a new one, so we send 'null' to
      //let the model make one up.
      return Application_Model_Cookie::makeNewCookie();
    }






}

