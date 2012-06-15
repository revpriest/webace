<?php

class CookieModelTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

  //Test The Cookie Model.
  public function testCookieModel(){
     $cookie = new Application_Model_Cookie();
     $cookie->setNick("testNick");
     $cookie->setEmail("testNick@mailinator.com");
     $cookie->setDisplayMode(1);
     $cookie->setSaveName('testNickSave');
     $cookie->setPassword('testNickPass');
     $cookie->setTwitter('testNickTwit');
     $cookie->setFacebook('testNickFace');
     $cookie->setCCEmail('1');
     if($cookie->getNick()!="testNick"){$this->fail("Nick SetterGetter Failed");}
     if($cookie->getEmail()!="testNick@mailinator.com"){$this->fail("Email SetterGetterFailed");}
     if($cookie->getDisplayMode()!=1){$this->fail("DisplayMode SetterGetterFailed");}
     if($cookie->getSaveName()!='testNickSave'){$this->fail("Savename GetterSetterFailed");}
     if($cookie->getPassword()!='testNickPass'){$this->fail("Password SetterGetterFailed");}
     if($cookie->getTwitter()!='testNickTwit'){$this->fail("Twitter SetterGetterFailed");}
     if($cookie->getFacebook()!='testNickFace'){$this->fail("Facebook SetterGetterFailed");}
     if($cookie->getCCEmail()!='1'){$this->fail("CCEmail SetterGetterFailed");}

     $rkey = $cookie->generateRandomKey();
     if($rkey == $cookie->generateRandomKey()){$this->fail("Random Keys Aren't Random They're '$rkey'");}
     if(strlen($rkey)!=60){$this->fail("Random Keys The Wrong Length, '$rkey' isn't 60 long");}
  }


  //Test the cookie Mapper.
  public function testCookieMapper(){
     $mapper = new Application_Model_CookieMapper();
     $cookie = new Application_Model_Cookie();
     $cookie->setNick("testNick");
     $cookie->setEmail("testNick@mailinator.com");
     $cookie->setDisplayMode(1);
     $cookie->setSaveName('testNickSave');
     $cookie->setPassword('testNickPass');
     $cookie->setTwitter('testNickTwit');
     $cookie->setFacebook('testNickFace');
     $cookie->setCCEmail('1');
     $mapper->save($cookie);
     $id = $cookie->getId();
     if(strlen($id)!=60){$this->fail("Saving Cookie didn't create ID properly");}

     $cookie = null;
     $cookie = $mapper->find($id);
     if($cookie==null){$this->fail("Can't retrieve saved cookie");}
     if($cookie->getId()!=$id){$this->fail("Saved Cookie Not Found");}

     $cookie = null;
     $cookie = $mapper->findFromPartial(substr($id,0,30));
     if($cookie==null){$this->fail("Can't retrieve saved cookie from partial");}
     if($cookie->getId()!=$id){$this->fail("Saved Cookie Not Found From Partial");}
   
     $cookie = null;
     $cookie = $mapper->findFromPassword("testNick@mailinator.com","testNickPass");
     if($cookie==null){$this->fail("Can't retrieve saved cookie from password");}
     if($cookie->getNick()!="testNick"){$this->fail("Saved Cookie Not Found From password");}
  
     $mapper->delete($cookie);
      
     $cookie = null;
     $cookie = $mapper->find($id);
     if($cookie!=null){$this->fail("Failed to delete cookie with id '$id'");}
  }
  

}
