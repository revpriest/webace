<?php
class Application_Model_EmailHashMapper {
    protected $_dbTable;
 
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
 
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_EmailHash');
        }
        return $this->_dbTable;
    }
 
    public function save(Application_Model_EmailHash $emailhash) {
        $data = array(
            'id'   => $emailhash->getId(),
            'cookie' => $emailhash->getCookie(),
            'email' => $emailhash->getEmail(),
            'hash' => $emailhash->getHash(),
            'created' => date('Y-m-d H:i:s'),
        );
 
        if (null === ($id = $emailhash->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
 
    public function find($hash, Application_Model_EmailHash $emailhash) {
      $select=$this->getDbTable()->select()->where("hash='".$hash."'");
      $result = $this->getDbTable()->fetchOne($select);
      if($result==null){return;}
      $row = $result->current();
      $emailhash->setId($row->id)
                ->setHash($row->hash)
                ->setEmail($row->email)
                ->setCookie($row->cookie)
                ->setCreated($row->created);
    }

}

