<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminController
 *
 * @author jb197342
 */
class AdminController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('read', 'html');
        $ajaxContext->addActionContext('edit', 'html');        
        $ajaxContext->initContext();        
    }
    
    public function indexAction() {
        
        $auth = Zend_Auth::getInstance();    
        if ($auth->hasIdentity()) {
            $role_id = $auth->getIdentity()->role_id;
            
            if ($role_id != 1) {
                $this->_helper->redirector('index', 'index', 'default');                                
            }
            
        } else {
            $this->_helper->redirector('index', 'index', 'default');                
        }
        
        $this->view->excel = "/admin/excel/table/";        
    }
    
    public function readAction()
    {

        $page = $this->_getParam('page', 1);
        $rows = $this->_getParam('rows', 5);
        $table_name = $this->_getParam("table");

        $grid = new JEB_Lib_Grid();
        $wheres = $grid->parseFilters();

        $session = new Zend_Session_Namespace();
        $session->__set("wheres", $wheres);
        
        $table_name_stripped = str_replace("view_", "", $table_name);

        $config = Zend_Registry::get('config');
        $columns = explode("|", $config->tablename->$table_name_stripped->columns);

        $service = new Application_Service_TableService();
        $entries = $service->fetchOutstanding($page, $wheres, $table_name, $columns, $rows);

        $this->view->response = $grid->writeXml($page, $rows, $entries);
        
    }
    
    public function excelAction()
    {

        $session = new Zend_Session_Namespace();
        $wheres = $session->__get("wheres");

        $table_name = $this->_getParam("table");
        $mapper = new Application_Model_TableMapper();

        $this->_helper->layout->disableLayout();
        $this->getResponse()->clearAllHeaders();

        $filename = 'rollsroycemc_'.date('YmdHis').".xls";
        $segment = $table_name;
        $rs = $mapper->getAll($table_name, $wheres);
        $user = JEB_Lib_Lib::getUser();

        $response = $this->getResponse();
        $name = "Content-Disposition";
        $value = "attachment; filename=\"".$filename."\"";
        $response->setHeader($name, $value);
        $name = "Content-Type";
        $value = "application/vnd.ms-excel";
        $response->setHeader($name, $value);
        
        $excel = new JEB_Lib_Excel();

        $content = $excel->writeExcelXml1($filename, $segment, $rs, $user);
        $response->setBody($content);
    }   
    
    
    public function editAction() {
        
        
        $config = Zend_Registry::get('config');
        $max = $config->session->attendee->max;
                
        $oper = $this->_getParam("oper");
        
        $id = $this->_getParam("id", 0);
        $date_created = date('Y-m-d');
        $last_updated = date('Y-m-d');
        $user = "system";
        $active = "1";
        
        $data = array(
            "date_created" => $date_created,
            "last_updated" => $last_updated,
            "user" => $user,
            "active" => $active,
        );
        
        $i = 0;
                
        if($this->getRequest()->isPost()) {
            
            $mapper = new Application_Model_TableMapper();
            $table_name = $this->_getParam("table");
            
            switch ($table_name) {
                
                case "users":
                    
                    //$data["role_id"] = $this->_getParam("role_id", 2);
                    $data["user_id"] = $this->_getParam("user_id");
                    $data["first_name"] = $this->_getParam("first_name");
                    $data["last_name"] = $this->_getParam("last_name");
                    //$data["email"] = $this->_getParam("email");
                    //$data["q_number"] = $this->_getParam("q_number", 0);
                    //$data["emp_id"] = $this->_getParam("emp_id", 0);
                    $data["title"] = $this->_getParam("title");
                    //$data["dealer_id"] = $this->_getParam("dealer_id");
                    //$data["location_id"] = $this->_getParam("location_id");
                    //$data["attending"] = $this->_getParam("attending");
                    //$data["registered_room"] = $this->_getParam("registered_room");
                    
                    break;
                
                case "attendees":
                    
                    $data["first_name"] = $this->_getParam("first_name");
                    $data["last_name"] = $this->_getParam("last_name");
                    $data["location_id"] = $this->_getParam("location_id", 0);
                    $data["manager_id"] = $this->_getParam("manager_id", 0);
                    $data["session_id"] = $this->_getParam("session_id", 0);
                    $data["dealer_id"] = $this->_getParam("dealer_id", 0);        
                    $data["title"] = $this->_getParam("title");
                    
                    break;
                
                case "locations":
                    
                    $data["name"] = $this->_getParam("name");
                    $data["description"] = $this->_getParam("description");
                    $data["hotel"] = $this->_getParam("hotel");
                    $data["hotel_url"] = $this->_getParam("hotel_url");
                    $data["start_date"] = $this->_getParam("start_date");
                    $data["end_date"] = $this->_getParam("end_date");
                    $data["group_code"] = $this->_getParam("group_code");
                    $data["negotitated_rate"] = $this->_getParam("negotitated_rate");
                    
                    break;
                
                case "dealerships":
                    
                    //$data["code"] = $this->_getParam("code");
                    $data["name"] = $this->_getParam("name");
                    $data["city"] = $this->_getParam("city");
                    $data["allocated_city"] = $this->_getParam("allocated_city");
                    $data["general_manager"] = $this->_getParam("general_manager");
                    
                    break;
                
                case "sessions":
                    
                    $data["name"] = $this->_getParam("name");
                    $data["description"] = $this->_getParam("description");
                    $data["schedule_date"] = $this->_getParam("schedule_date");
                    $data["start"] = $this->_getParam("start");
                    $data["end"] = $this->_getParam("end");
                    $data["room"] = $this->_getParam("room");
                    
                    break;
                
                case "participants":
                    
                    $data["first_name"] = $this->_getParam("first_name");
                    $data["last_name"] = $this->_getParam("last_name");
                    $data["title"] = $this->_getParam("title");
                    
                    break;
                
                case "product_training":
                    
                    $data["name"] = $this->_getParam("name");
                    $data["description"] = $this->_getParam("description");
                    $data["where"] = $this->_getParam("where");
                    $data["why"] = $this->_getParam("why");
                    $data["when"] = $this->_getParam("when");
                    
                    break;

                default:
                    
                    break;
                
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
    
        
}

?>
