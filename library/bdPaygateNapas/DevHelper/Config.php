<?php

class bdPaygateNapas_DevHelper_Config extends DevHelper_Config_Base
{
    protected $_dataClasses = array(
        'order' => array(
            'name' => 'order',
            'camelCase' => 'Order',
            'camelCasePlural' => false,
            'camelCaseWSpace' => 'Order',
            'camelCasePluralWSpace' => false,
            'fields' => array(
                'order_id' => array('name' => 'order_id', 'type' => 'uint', 'autoIncrement' => true),
                'item_id' => array('name' => 'item_id', 'type' => 'string', 'required' => true),
                'return_url' => array('name' => 'return_url', 'type' => 'string'),
            ),
            'phrases' => array(),
            'title_field' => 'item_id',
            'primaryKey' => array('order_id'),
            'indeces' => array(),
            'files' => array(
                'data_writer' => array(
                    'className' => 'bdPaygateNapas_DataWriter_Order',
                    'hash' => '7729547bfbe183baa29516b4f3169dfa',
                ),
                'model' => array(
                    'className' => 'bdPaygateNapas_Model_Order',
                    'hash' => 'dde211eec9988aa82233a5800c235049',
                ),
                'route_prefix_admin' => false,
                'controller_admin' => false,
            ),
        ),
    );
    protected $_dataPatches = array();
    protected $_exportPath = false;
    protected $_exportIncludes = array();
    protected $_exportExcludes = array();
    protected $_exportAddOns = array();
    protected $_exportStyles = array();
    protected $_options = array();

    /**
     * Return false to trigger the upgrade!
     **/
    protected function _upgrade()
    {
        return true; // remove this line to trigger update

        /*
        $this->addDataClass(
            'name_here',
            array( // fields
                'field_here' => array(
                    'type' => 'type_here',
                    // 'length' => 'length_here',
                    // 'required' => true,
                    // 'allowedValues' => array('value_1', 'value_2'),
                    // 'default' => 0,
                    // 'autoIncrement' => true,
                ),
                // other fields go here
            ),
            array('primary_key_1', 'primary_key_2'), // or 'primary_key', both are okie
            array( // indeces
                array(
                    'fields' => array('field_1', 'field_2'),
                    'type' => 'NORMAL', // UNIQUE or FULLTEXT
                ),
            ),
        );
        */
    }
}
