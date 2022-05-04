<?php

class bdPaygateNapas_DataWriter_Order extends XenForo_DataWriter
{

    /* Start auto-generated lines of code. Change made will be overwritten... */

    protected function _getFields()
    {
        return array(
            'xf_bdpaygatenapas_order' => array(
                'order_id' => array('type' => XenForo_DataWriter::TYPE_UINT, 'autoIncrement' => true),
                'item_id' => array('type' => XenForo_DataWriter::TYPE_STRING, 'required' => true),
                'return_url' => array('type' => XenForo_DataWriter::TYPE_STRING),
            )
        );
    }

    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'order_id')) {
            return false;
        }

        return array('xf_bdpaygatenapas_order' => $this->_getOrderModel()->getOrderById($id));
    }

    protected function _getUpdateCondition($tableName)
    {
        $conditions = array();

        foreach (array('order_id') as $field) {
            $conditions[] = $field . ' = ' . $this->_db->quote($this->getExisting($field));
        }

        return implode(' AND ', $conditions);
    }

    protected function _getOrderModel()
    {
        /** @var bdPaygateNapas_Model_Order $model */
        $model = $this->getModelFromCache('bdPaygateNapas_Model_Order');

        return $model;
    }

    /* End auto-generated lines of code. Feel free to make changes below */
}
