<?php

class CommentControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    public function testIndexAction()
    {
        $params = array('action' => 'index', 'controller' => 'Comment', 'module' => 'default');
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'div#content h1',
            'Latest Messages'
            );
    }

    public function testSubmitAction()
    {
        $params = array('action' => 'submit', 'controller' => 'Comment', 'module' => 'default');
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'dt#url-label',
            'The URL of the page you are commenting on:'
            );
    }


    public function testPollAction()
    {
        $params = array('action' => 'poll', 'controller' => 'Comment', 'module' => 'default');
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $json = $this->getResponse()->getBody();
        if(!preg_match("/comments.*success.*setCookie.*url/",$json)){
           $this->fail("Poll system failing to return JSON.");
        }
    }

    public function testHotconversationsAction()
    {
        $params = array('action' => 'hotconversations', 'controller' => 'Comment', 'module' => 'default');
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'div#content h1',
            'Hot Conversations'
            );
    }

    public function testUserAction()
    {
        $m = new Application_Model_CommentMapper();
        $mid = $m->getMaxMessageId();
        
        $params = array('action' => 'user', 'controller' => 'Comment', 'module' => 'default', 'mid'=>$mid);
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'h1#userTitle',
            "User's Messages:"
            );
    }

    public function testShowAction()
    {
        $m = new Application_Model_CommentMapper();
        $id = $m->getMaxMessageId();
        $params = array('action' => 'show', 'controller' => 'Comment', 'module' => 'default', 'id'=> $id);
        $url = $this->url($this->urlizeOptions($params));
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($params['module']);
        $this->assertController($params['controller']);
        $this->assertAction($params['action']);
        $this->assertQueryContentContains(
            'div#content h3',
            'Single Message'
            );
    }


}















