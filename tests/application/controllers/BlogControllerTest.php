<?php

class BlogControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    public function testIndexAction()
    {
        $params = array('action' => 'index', 'controller' => 'Blog', 'module' => 'default');
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'div#content h1',
            'News'
            );
    }

    public function testShowAction()
    {
        //Grab a blog file to test with, any old random one.
        $blogs = Application_Model_Blog::getBlogList();
        if(sizeof($blogs)<0){
          return;
        }
    
        $params = array('action' => 'show', 'controller' => 'Blog', 'module' => 'default', 'id'=>$blogs[0]['file']);
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'div#header-logo h1',
            'WebAce'
            );
    }


}





