<?php
/***********************************************
* Data Mapper for the Comment table. Map the
* model to the data source.
*/ 

class Application_Model_CommentMapper{
    protected $_dbTable;

    /***********************************************
    * Set which DB Table we're using. This ought
    * to always be "comment" I would think so not
    * sure why we're taking a parameter for it.
    * Boilerplate code from the tutorials.
    */
    public function setDbTable($dbTable){
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /****************************************
    * Get DB Table
    */ 
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Comment');
        }
        return $this->_dbTable;
    }

    /******************************************************
    * Save the data from the model into the database table
    */ 
    public function save(Application_Model_Comment $comment) {
        $data = array(
            'domain'   => $comment->getDomain(),
            'path'     => $comment->getPath(),
            'cookie'   => $comment->getCookie(),
            'nick'     => $comment->getNick(),
            'email'    => $comment->getEmail(),
            'ip'       => $comment->getIp(),
            'content'  => $comment->getContent(),
            'reply_to' => $comment->getReplyTo(),
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
        );
 
        if (null === ($id = $comment->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            unset($data['created']);    //Added by Pre: Don't want to change Created date when updating.
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /*********************************************************
    * Find a database table's row and hydrate it into a
    * model object. Surprisingly we seem to be setting
    * data on an object already created and passed as a
    * param rather than just creating the object and
    * returning it. Wonder why that is? 
    */ 
    public function find($id, Application_Model_Comment $comment) {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $comment->setId($row->id)
                ->setDomain($row->domain)
                ->setPath($row->path)
                ->setCookie($row->cookie)
                ->setNick($row->nick)
                ->setEmail($row->email)
                ->setIp($row->ip)
                ->setContent($row->content)
                ->setReplyTo($row->reply_to)
                ->setCreated($row->created)
                ->setUpdated($row->updated);
    }


    /*************************************************
    * Find a bunch of rows based on a where clause
    * with various limts and offsets and whatnot
    */
    public function findWhere($where,$limit=5,$offset=0,$order="id desc"){
      $select=$this->getDbTable()->select()->where($where)->order($order)->limit($limit,$offset);
      return $this->getDbTable()->fetchAll($select);
    }

    /***************************************************
    * Grab everything from the DB table and put it into
    * an array of model objects. This has a lot of
    * ->setX($row->X) lines which are duplicated above.
    * Maybe they sould be taken in
        $comment = to a function of their
    * own?
    */ 
    public function fetchAll() {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = $this->convertRowToObject($row);
            $entries[] = $entry;
        }
        return $entries;
    }


    /************************************************
    * Convert a database row into a proper object to
    * return
    */
    public function convertRowToObject($row){
      $entry = new Application_Model_Comment();
      $entry->setId($row->id)
            ->setDomain($row->domain)
            ->setPath($row->path)
            ->setCookie($row->cookie)
            ->setNick($row->nick)
            ->setEmail($row->email)
            ->setIp($row->ip)
            ->setContent($row->content)
            ->setReplyTo($row->reply_to)
            ->setCreated($row->created)
            ->setUpdated($row->updated);
       return $entry;
    }

    /************************************************
    * Convert a database row into an array
    */
    public function convertRowToArray($row){
      $entry = array();
      $entry['id']     =$row->id;
      $entry['domain'] =$row->domain;
      $entry['path']   =$row->path;
      $entry['cookie'] =$row->cookie;
      $entry['nick']   =$row->nick;
      $entry['email']  =$row->email;
      $entry['ip']     =$row->ip;
      $entry['content']=$row->content;
      $entry['replyto']=$row->reply_to;
      $entry['created']=$row->created;
      $entry['updated']=$row->updated;
       return $entry;
    }
}

