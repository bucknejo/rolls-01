<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RegistrationController
 *
 * @author jb197342
 */
class RegistrationController extends Zend_Controller_Action {
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('allotment', 'html');
        $ajaxContext->addActionContext('attending', 'html');
        $ajaxContext->addActionContext('detail', 'html');
        $ajaxContext->addActionContext('edit', 'html');
        $ajaxContext->addActionContext('participant', 'html');
        $ajaxContext->addActionContext('read', 'html');
        $ajaxContext->addActionContext('registered', 'html');
        $ajaxContext->addActionContext('submission', 'html');
        $ajaxContext->addActionContext('toggle', 'html');
        $ajaxContext->addActionContext('total', 'html');
        $ajaxContext->initContext();        
    }
    
    public function indexAction() {
        
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
            
            $table_name = "locations";            
            $locations = $mapper->getItemById($table_name, $location_id);
            
            $table_name = "sessions";
            $wheres = array();
            $wheres[] = "location_id = $location_id";
            $sessions = $mapper->getAll($table_name, $wheres);
            
            $table_name = "dealerships";
            $wheres = array();
            $wheres[] = "id = $dealer_id";
            $dealerships = $mapper->getAll($table_name, $wheres);
            
            $i = 0;
            foreach($sessions as $session) {
                $session_id = $session["id"];
                $table_name = "attendees";
                $attendees = $mapper->getItemById($table_name, $session_id);
                $sessions[$i]["attendees"] = $attendees;
                $wheres = array();
                $wheres[] = "session_id = $session_id";
                $total = $mapper->getAll($table_name, $wheres);
                $sessions[$i]["total"] = $total;
                $i++;
            }
            
            $table_name = "product_training";
            $wheres = array();
            $wheres[] = "location_id = $location_id";
            $product_trainings = $mapper->getAll($table_name, $wheres);
            
            $table_name = "participants";
            $wheres = array();
            $wheres[] = "dealer_id = $dealer_id";
            $participants = $mapper->getAll($table_name, $wheres);
            
            $config = Zend_Registry::get('config');
            $max = $config->session->attendee->max;
            $dealerMax = $config->session->dealer->max;
                
            $this->view->users = $users;
            $this->view->locations = $locations;
            $this->view->sessions = $sessions;
            $this->view->dealerships = $dealerships;
            $this->view->product_trainings = $product_trainings;
            $this->view->participants = $participants;
            $this->view->max = $max;
            $this->view->dealerMax = $dealerMax;
            
                                    
        } catch (Exception $e) {

        }
        
    }
    
    public function readAction() {
        
        $page = $this->_getParam('page', 1);
        $rows = $this->_getParam('rows', 5);
        $table_name = $this->_getParam('table', '');
        
        $session_id = $this->_getParam('session_id', 0);
        $dealer_id = $this->_getParam('dealer_id', 0);
        
        $grid = new JEB_Lib_Grid();
        $wheres = $grid->parseFilters();
        
        if ($table_name == 'attendees') {
            $wheres[] = "session_id = $session_id";
            $wheres[] = "dealer_id = $dealer_id";
        }

        if ($table_name == 'participants') {
            $wheres[] = "dealer_id = $dealer_id";
        }

        $session = new Zend_Session_Namespace();
        $session->__set("wheres", $wheres);

        $config = Zend_Registry::get('config');
        $columns = explode("|", $config->tablename->$table_name->columns);

        $service = new Application_Service_TableService();
        $entries = $service->fetchOutstanding($page, $wheres, $table_name, $columns, $rows);

        $this->view->response = $grid->writeXml($page, $rows, $entries);
        
        
    }
    
    public function editAction() {
        
        $oper = $this->_getParam("oper");
        
        $table_name = $this->_getParam("table", "");        
        $id = $this->_getParam("id", 0);
        
        $date_created = date('Y-m-d');
        $last_updated = date('Y-m-d');
        $user = "system";
        $active = "1";
        $first_name = $this->_getParam("first_name");
        $last_name = $this->_getParam("last_name");
        $location_id = $this->_getParam("location_id", 0);
        $manager_id = $this->_getParam("manager_id", 0);
        $session_id = $this->_getParam("session_id", 0);
        $dealer_id = $this->_getParam("dealer_id", 0);        
        $title = $this->_getParam("title");
        
        $data = array(
            "date_created" => $date_created,
            "last_updated" => $last_updated,
            "user" => $user,
            "active" => $active,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "location_id" => $location_id,
            "dealer_id" => $dealer_id,
            "title" => $title
        );
        
        $i = 0;
                
        if($this->getRequest()->isPost()) {
            
            $mapper = new Application_Model_TableMapper();
            
            if ($table_name == "attendees") {
                $data["manager_id"] = $manager_id;
                $data["session_id"] = $session_id;
            }
                                    
            if ($oper == "add") {                
                $i = $mapper->insertItem($table_name, $data);                    
            } else if ($oper == "edit") {                
                $i = $mapper->updateItem($table_name, $data, $id);
            } else {                
                $i = $mapper->deleteItem($table_name, $id);                
            }
            
        }
        
        $this->view->i = $i;
        
                        
    }
    
    public function attendingAction() {
        
        try {
            
            $i = 0;
            
            if($this->getRequest()->isPost()) {
                
                $id = $this->_getParam("id");
                $attnd = $this->_getParam("attnd");
                $dealer_id = $this->_getParam("dealer_id");
                
                $config = Zend_Registry::get('config');
                $dealerMax = $config->session->dealer->max;
                                
                $mapper = new Application_Model_TableMapper();
                $table_name = "participants";
                
                $wheres = array();
                $wheres[] = "dealer_id = $dealer_id";
                $participants = $mapper->getAll($table_name, $wheres);
                                
                if (($attnd == 1) && (count($participants) >= $dealerMax)) {
                    $attending = -1;
                } else {
                    
                    $table_name = "users";


                    $data = array(
                        "attending" => $attnd
                    );

                    $i = $mapper->updateItem($table_name, $data, $id);

                    if ($i != 0) {
                        $users = $mapper->getItemById($table_name, $id);
                        $attending = $users[0]["attending"];
                    }
                    
                }
                
            }
            
            $this->view->attending = $attending;
            $this->view->i = $i;
            
            
        } catch (Exception $e) {

        }
        
    }
    
    public function registerAction() {
        
        try {
            
            $i = 0;
            
            if($this->getRequest()->isPost()) {

                $mapper = new Application_Model_TableMapper();
                $table_name = "users";

                $id = $this->_getParam("id");
                $room = $this->_getParam("room");

                $data = array(
                    "registered_room" => $room
                );

                $i = $mapper->updateItem($table_name, $data, $id);

            }
            
            $this->view->i = $i;
            
            
        } catch (Exception $e) {

        }
        
    }
    
    public function confirmAction() {
                
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
            
            $table_name = "locations";            
            $locations = $mapper->getItemById($table_name, $location_id);
            
            $table_name = "sessions";
            $wheres = array();
            $wheres[] = "location_id = $location_id";
            $sessions = $mapper->getAll($table_name, $wheres);
            
            $table_name = "dealerships";
            $wheres = array();
            $wheres[] = "id = $dealer_id";
            $dealerships = $mapper->getAll($table_name, $wheres);
            
            $i = 0;
            foreach($sessions as $session) {
                $session_id = $session["id"];
                $table_name = "attendees";
                $wheres = array();
                $wheres[] = "session_id = $session_id";
                $wheres[] = "dealer_id = $dealer_id";
                $attendees = $mapper->getAll($table_name, $wheres);
                $sessions[$i]["attendees"] = $attendees;
                $i++;
            }
            
            $table_name = "addresses";
            $wheres = array();
            $wheres[] = "id = $location_id";
            $addresses = $mapper->getAll($table_name, $wheres);
            
            
            $this->view->users = $users;
            $this->view->locations = $locations;
            $this->view->sessions = $sessions;
            $this->view->dealerships = $dealerships;
            $this->view->addresses = $addresses;
            
                                    
        } catch (Exception $e) {

        }
            
    }
    
    public function totalAction() {
        
        $session_id = $this->_getParam("session_id");
        
        $mapper = new Application_Model_TableMapper();

        $table_name = "attendees";
        $wheres = array();
        $wheres[] = "session_id = $session_id";
        $attendees = $mapper->getAll($table_name, $wheres);
        $this->view->total = count($attendees);
                
    }
    
    public function allotmentAction() {
        
        $session_id = $this->_getParam("session_id");
        $dealer_id = $this->_getParam("dealer_id");
        
        $mapper = new Application_Model_TableMapper();

        $table_name = "attendees";
        $wheres = array();
        $wheres[] = "session_id = $session_id";
        $wheres[] = "dealer_id = $dealer_id";
        $attendees = $mapper->getAll($table_name, $wheres);
        $this->view->total = count($attendees);
                
    }
    
    public function participantAction() {
        
        $dealer_id = $this->_getParam("dealer_id");
        
        $mapper = new Application_Model_TableMapper();

        $table_name = "participants";
        $wheres = array();
        $wheres[] = "dealer_id = $dealer_id";
        $participants = $mapper->getAll($table_name, $wheres);
        $this->view->total = count($participants);
                
    }
    
    public function toggleAction() {
        
        $status = $this->_getParam("status");
        
        $mapper = new Application_Model_TableMapper();
        $table_name = "participants";
        
        $date_created = date('Y-m-d');
        $last_updated = date('Y-m-d');
        $user = "system";
        $active = "1";
        $first_name = $this->_getParam("first_name");
        $last_name = $this->_getParam("last_name");
        $title = $this->_getParam("title");
        $dealer_id = $this->_getParam("dealer_id");
        $location_id = $this->_getParam("location_id");
        
        $data = array(
            "date_created" => $date_created,
            "last_updated" => $last_updated,
            "user" => $user,
            "active" => $active,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "title" => $title,
            "dealer_id" => $dealer_id,
            "location_id" => $location_id
        );
        
        $i = 0;
        
        if ($status == 0) {
            // remove
            $wheres = array(
                "first_name = ?" => $first_name,
                "last_name = ?" => $last_name,
                "dealer_id = ?" => $dealer_id
            );
            $i = $mapper->deleteItemByCriteria($table_name, $wheres);
            
        } else if ($status == 1) {
            // add
            $i = $mapper->insertItem($table_name, $data);
        }
        
        
        $this->view->i = $i;
        
    }
    
    public function submissionAction() {
        
        $id = $this->_getParam("id");
                
        $this->view->total = $id;
        
    }
    
    
}

?>
