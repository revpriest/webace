<?php
/***************************************************
* The form for submitting comments. Even though
* this will need to be all done by the javascript
* chat app, we'll start by using a simple form here.
* 
* Yet another almost empty file created by the zf
* script would you would THINK could have had all
* the DB fields included but, no, it's just the
* empty class. We'll have to add all the DB
* fields ourselves.
*/

class Application_Form_Comment extends Zend_Form {

    /******************************************
    * Create the form
    */
    public function init() {
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        // Add a URL element
        $this->addElement('text', 'url', array(
            'label'      => 'The URL of the page you are commenting on:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array()
        ));
 
        // Add the comment element
        $this->addElement('textarea', 'content', array(
            'label'      => 'Your comment:',
            'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 10240))
                )
        ));

        // Add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Attach Comment',
        ));
    }


}

