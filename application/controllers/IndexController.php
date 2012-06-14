<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
       $this->view->page=$this->getRequest()->getParam('page');
       if($this->view->page==null){
         $this->view->page = "welcome";
       }
       $this->view->title = ucfirst($this->view->page);
    }

    public function logoutAction()
    {
        /*****************************************************
        * Logout, just clear the cookie basically.
        */
        $this->view->title="Logout";
        try{
          setcookie('cookieKey',"Logout",time()+(7*24*60*60),"/");
        }catch(Exception $e){
          //Can't set cookie? probably coz we're not in a browser,
          //we're just running a unit test. Ignore that.
        }
    }

    public function launchAction()
    {
        /*****************************************************
        * Send a URL over to the view.
        */
        $this->_helper->layout()->disableLayout();
        $this->view->url=$this->getRequest()->getParam('url');
        $this->view->title="Forwarding To ".$this->view->url;
    }


}









