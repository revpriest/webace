<?php
/****************************************************************
* WARNING: 
* EWWWW! NASTY, NASTY! When ever we call the zf add controller
* action function, it CLEARS COMMENTS FROM THIS CODE.
*
* The blog system is designed to be utterly trivial and
* simple for me, Pre, to use. This one is NOT designed
* to be utterly trivial or simple to just anyone, only
* to ME: Someone who is shelled into the server most of
* the time anyway, and is more comfortable writing text
* in Vim than in Word or some weird in-browser editing
* form.
*
* I want to update the blog by typing:
* $ vim blogs/YYYYMMDD-Title.phtml
* Then type some text, with html and even Php in
* there if I want it, then save and THAT'S IT!
* If I want to save a draft I'll call it
* .draft instead of .phtml
* It'll use the WebAce system itself for any
* comments from users.
*
* Like I say, utterly trivial, so long as you already
* know how to use Vim, and are shelled into the
* server 24 hours a day anyway. 
*
* I read my email on the server with mutt too you know.
*
* Anyway: Onwards
*/

class BlogController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public static function listBlogs()
    {
      /***************************************************************
      * Fetch an array with all the blogs in it.
      */
       $blogs = Application_Model_Blog::getBlogList();
       return $blogs;
    }

    public function indexAction()
    {
       /********************************************************
       * Basicially: ls -l blogs/*-*.phtml
       */
       $this->view->blogs = $this->listBlogs();
    }

    public function showAction()
    {
       /*******************************************************
       * Show a particular blog
       */
       $file = $this->getRequest()->getParam('id');
       $file = preg_replace("/[^A-Za-z0-9\.\-]*/","",$file);
       $this->view->blogfile = "blogs/".$file;
    }


}



