<?php
/*************************************************************
* We make a new version of the CSRF which needs to allow
* the user to operate in more than one tab
*/
require_once 'Zend/Form/Element/Xhtml.php';
class Webace_Form_Element_WebaceCSRF extends Zend_Form_Element_Hash
{

    protected function _generateHash()
    {
      /*****************************************************
      * If we generate entirely random hashes, then multiple
      * windows all get random hashes and if they reply out of
      * order things get horribly wrong and broken. We need
      * to generate a random hash which will continue to work
      * for a few minutes whatever order the windows respond.
      *
      * So we work off the current date, which should give us
      * a hash that only changes every ten minutes. Throw in
      * some salt the session cookie and it should hopefully
      * work, more or less.
      *
      * It may fail if it spills over the ten-minute border
      * in the clocks at the same time as the user sends
      * more than one request from different windows.
      *
      * We should add in some code to the client to handle
      * CSRF rejections more cleanly with a retry, basically.
      */ 
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $opts = $bootstrap->getOptions();
        $salt = $opts['webace']['csrfSalt'];

        $this->_hash = md5(
           substr(date('iYMdH'),1).
           $_COOKIE['cookieKey'].
           $salt
        );
        $this->setValue($this->_hash);
    }

}
