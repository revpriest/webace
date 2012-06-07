<?php
/*****************************************************
* A custom validator to check the CSRF. It's
* bascially "inArray" only if it finds itself
* returning TRUE then it deletes the row in the
* database table so re-using that key won't work.
*/
class Application_Form_WebaceCSRFValidator extends Zend_Validate_InArray{


    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_IN_ARRAY => "'%value%' is an invalid CSRF token. Try submitting the form more quickly.",
    );

    public function isValid($value) {
      $ret = parent::isValid($value);
      if($ret){
        $mapper = new Application_Model_CsrfhashMapper();
        $mapper->deleteByCsrf($value);
      }
      return $ret;
    }

}
    
