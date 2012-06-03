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
       $this->view->title="Welcome";
    }

    public function logoutAction()
    {
        /*****************************************************
        * Logout, just clear the cookie basically.
        */
        $this->view->title="Logout";
        setcookie('cookieKey',"Logout",time()+(7*24*60*60),"/");
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









