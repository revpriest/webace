<?php
/*****************************************************************
* A controller. This was auto-generated but again, with
* basically no content at all. Every action needs to
* be added by hand, individually, at least it was in
* this tutorial. Perhaps having Doctrine around would
* make it easier?
*
* EWWWW! NASTY, NASTY! When ever we call the zf add controller
* action function, it CLEARS COMMENTS FROM THIS CODE.
* My normal before-the-function description of 
* the function gets erased. We need to put all that
* stuff INSIDE the function. At least it's not
* deleting extra functions I guess but that's nasty.
* Nasty. Nasty. Nasty.
*/
class CommentController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction()
    {
        $comment = new Application_Model_CommentMapper();
        $this->view->comments = $comment->fetchAll();
    }

    public function formDataToObjectData($vals){
      /*****************************************************************
      * A form has a single 'url' field, and a comment object
      * has separate 'domain' and 'path' fields. There are doubtless
      * other differences too. Here we convert that form data into
      * something that'll instantiate a comment model object.
      */
      if(substr($vals['url'],0,7)!="http://"){
        return "Comment can't be attached to invalid URL";
      }
      $firstslash=strpos($vals['url'],'/',7);
      if($firstslash===false){
        return "Comment can't be attached to invalid URL.";
      }
      $vals['domain']=substr($vals['url'],7,$firstslash-7);
      $vals['path']=substr($vals['url'],$firstslash+1);
      $cookie = Application_Model_DbTable_Cookie::getUserCookie();
      $vals['cookie']=$cookie->getId();
      $vals['nick']=$cookie->getNick();
      $vals['email']=$cookie->getEmail();
      $vals['ip']=$_SERVER['REMOTE_ADDR'];
      return $vals;
    }

    public function submitAction()
    {
        $request = $this->getRequest();
        $form    = new Application_Form_Comment();
 
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $initVals = $form->getValues();
                $initVals = $this->formDataToObjectData($initVals);
                if(!is_array($initVals)){
                    print "Error: $initVals";
                    exit;
                }
                $comment = new Application_Model_Comment($initVals);
                $mapper  = new Application_Model_CommentMapper();
                $mapper->save($comment);
                return $this->_helper->redirector('index');
            }
        }
        $this->view->form = $form;
    }



}


