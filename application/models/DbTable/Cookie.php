<?php

class Application_Model_DbTable_Cookie extends Zend_Db_Table_Abstract
{
    protected $_name = 'cookie';

    public static function getUserCookie(){
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
      if((is_string($cookieKey))&&(strlen($cookieKey)>10)){
        //Look up a hopefully existing cookie.
        $cookie = new Application_Model_CookieMapper();
        $cookie=$cookie->find($cookieKey);
        if($cookie!=null){
           setcookie('cookieKey',$cookie->getId(),time()+(7*24*60*60),"/");
           return $cookie;
        }
      }
      //For one reason or another we can't trace their
      //Cookie. Best make a new one, so we send 'null' to
      //let the model make one up.
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

