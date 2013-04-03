<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
        $user_id = $this->_getParam('user_id', 'Missing User ID');
        $password = $this->_getParam('password', '');
        
        $table_name = 'users';

        $message = "";

        $user_id = stripslashes($user_id);
        
        $values = array(
            'user_id' => $user_id,
            'password' => $password,
        );


        if ($this->getRequest()->isPost()) {

            if ($this->_process($table_name, $values)) {
                $auth = Zend_Auth::getInstance();
                $role_id = $auth->getIdentity()->role_id;
                $id = $auth->getIdentity()->id;
                
                if ($role_id == 1) {
                    $this->_helper->redirector('index', 'admin', 'default');
                } else {
                    $this->_helper->redirector('welcome', 'index', 'default');
                }
                
            } else {
                $message = "Sorry!  We cannot authenticate: " . $values['user_id'];
            }
        } else {
            
        }

        $this->view->message = $message;
        
    }
    
    protected function _process($table_name, $values) {

        $adapter = $this->_getAuthAdapter($table_name);
        
        $adapter->setIdentity($values['user_id']);
        $adapter->setCredential($values['password']);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            $user = $adapter->getResultRowObject();
            $auth->getStorage()->write($user);
            $session = new Zend_Session_Namespace('Zend_Auth');
            $session->setExpirationSeconds(30 * 60);
            return true;
        }
        return false;
    }

    protected function _getAuthAdapter($table_name) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName($table_name);
        
        $authAdapter->setIdentityColumn('user_id');
        $authAdapter->setCredentialColumn('password');

//        $authAdapter->setIdentityColumn('email');
//        $authAdapter->setCredentialColumn('q_number');
        
        return $authAdapter;
    }
    
    public function _decrypt($password, $user_id, $key) {
        $mapper = new Application_Model_TableMapper();
        $column = new Zend_Db_Expr("AES_DECRYPT(password,'$key')");
        $where = "user_id = '$user_id'";
        $decrypt = $mapper->getSingleColumnValue('users', $column, $where);

        return $decrypt;
    }
    
    public function welcomeAction() {
        
        try {
            
            $auth = Zend_Auth::getInstance();    
            if ($auth->hasIdentity()) {
                $id = $auth->getIdentity()->id;
                $location_id = $auth->getIdentity()->location_id;
                $dealer_id = $auth->getIdentity()->dealer_id;
            } else {
                $this->_helper->redirector('index', 'index', 'default');                
            }
            
            $mapper = new Application_Model_TableMapper();
            
            $table_name = "users";
            $users = $mapper->getItemById($table_name, $id);
                            
            $this->view->users = $users;
                                    
        } catch (Exception $e) {

        }
        
        
    }
    


}

