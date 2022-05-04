<?php

class bdPaygateNapas_Model_Order extends XenForo_Model
{

    /* Start auto-generated lines of code. Change made will be overwritten... */

    public function getList(array $conditions = array(), array $fetchOptions = array())
    {
        $allOrder = $this->getAllOrder($conditions, $fetchOptions);
        $list = array();

        foreach ($allOrder as $id => $order) {
            $list[$id] = $order['item_id'];
        }

        return $list;
    }

    public function getOrderById($id, array $fetchOptions = array())
    {
        $allOrder = $this->getAllOrder(array('order_id' => $id), $fetchOptions);

        return reset($allOrder);
    }

    public function getOrderIdsInRange($start, $limit)
    {
        $db = $this->_getDb();

        return $db->fetchCol($db->limit('
            SELECT order_id
            FROM xf_bdpaygatenapas_order
            WHERE order_id > ?
            ORDER BY order_id
        ', $limit), $start);
    }

    public function getAllOrder(array $conditions = array(), array $fetchOptions = array())
    {
        $whereConditions = $this->prepareOrderConditions($conditions, $fetchOptions);

        $orderClause = $this->prepareOrderOrderOptions($fetchOptions);
        $joinOptions = $this->prepareOrderFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        $allOrder = $this->fetchAllKeyed($this->limitQueryResults("
            SELECT _order.*
                $joinOptions[selectFields]
            FROM `xf_bdpaygatenapas_order` AS _order
                $joinOptions[joinTables]
            WHERE $whereConditions
                $orderClause
        ", $limitOptions['limit'], $limitOptions['offset']), 'order_id');

        $this->_getAllOrderCustomized($allOrder, $fetchOptions);

        return $allOrder;
    }

    public function countAllOrder(array $conditions = array(), array $fetchOptions = array())
    {
        $whereConditions = $this->prepareOrderConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareOrderFetchOptions($fetchOptions);

        return $this->_getDb()->fetchOne("
            SELECT COUNT(*)
            FROM `xf_bdpaygatenapas_order` AS _order
                $joinOptions[joinTables]
            WHERE $whereConditions
        ");
    }

    public function prepareOrderConditions(array $conditions = array(), array $fetchOptions = array())
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (isset($conditions['order_id'])) {
            if (is_array($conditions['order_id'])) {
                if (!empty($conditions['order_id'])) {
                    // only use IN condition if the array is not empty (nasty!)
                    $sqlConditions[] = "_order.order_id IN (" . $db->quote($conditions['order_id']) . ")";
                }
            } else {
                $sqlConditions[] = "_order.order_id = " . $db->quote($conditions['order_id']);
            }
        }

        $this->_prepareOrderConditionsCustomized($sqlConditions, $conditions, $fetchOptions);

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareOrderFetchOptions(array $fetchOptions = array())
    {
        $selectFields = '';
        $joinTables = '';

        $this->_prepareOrderFetchOptionsCustomized($selectFields, $joinTables, $fetchOptions);

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function prepareOrderOrderOptions(array $fetchOptions = array(), $defaultOrderSql = '')
    {
        $choices = array();

        $this->_prepareOrderOrderOptionsCustomized($choices, $fetchOptions);

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    /* End auto-generated lines of code. Feel free to make changes below */

    protected function _getAllOrderCustomized(array &$data, array $fetchOptions)
    {
        // customized code goes here
    }

    protected function _prepareOrderConditionsCustomized(array &$sqlConditions, array $conditions, array $fetchOptions)
    {
        // customized code goes here
    }

    protected function _prepareOrderFetchOptionsCustomized(&$selectFields, &$joinTables, array $fetchOptions)
    {
        // customized code goes here
    }

    protected function _prepareOrderOrderOptionsCustomized(array &$choices, array &$fetchOptions)
    {
        // customized code goes here
    }
}
