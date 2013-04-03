<?php
class Application_Service_TableService
{
    public function fetchOutstanding($page, $wheres, $table_name, $columns, $numberPerPage=25) {
        $mapper = new Application_Model_TableMapper();
        $entries = $mapper->fetchOutstanding($wheres, $table_name, $columns);
        $entries->setCurrentPageNumber($page);
        $entries->setItemCountPerPage($numberPerPage);
        return $entries;
    }
        
}
?>
