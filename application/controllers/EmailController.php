<?php

class EmailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function confirmAction(){
       $mapper = new Application_Model_EmailHashMapper();
       $hashHash = addslashes($this->getRequest()->getParam('hash'));
       $hash = new Application_Model_EmailHash();
       $mapper->find($hashHash,$hash);
       if($hash->getCookie()!=null){
          $this->view->hash = $hash;
          $cookieObj = $hash->fetchCookieObject();
          $cookieObj->setEmail($hash->getEmail()); 
          $cookieMapper = new Application_Model_CookieMapper();
          $cookieMapper->save($cookieObj);
       }else{
          $this->view->hash = false;
       }
      
    }


}



