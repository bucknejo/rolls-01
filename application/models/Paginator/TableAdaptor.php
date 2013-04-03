<?php
class Application_Model_Paginator_TableAdaptor extends Zend_Paginator_Adapter_DbSelect
{
    public function getItems($offset, $itemCountPerPage) {
        $rows = parent::getItems($offset, $itemCountPerPage);
        return $rows;
    }
}
?>
