<?php

class bdPaygateNapas_Installer
{
    /* Start auto-generated lines of code. Change made will be overwritten... */

    protected static $_tables = array(
        'order' => array(
            'createQuery' => 'CREATE TABLE IF NOT EXISTS `xf_bdpaygatenapas_order` (
                `order_id` INT(10) UNSIGNED AUTO_INCREMENT
                ,`item_id` TEXT NOT NULL
                ,`return_url` TEXT
                , PRIMARY KEY (`order_id`)
                
            ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
            'dropQuery' => 'DROP TABLE IF EXISTS `xf_bdpaygatenapas_order`',
        ),
    );
    protected static $_patches = array();

    public static function install($existingAddOn, $addOnData)
    {
        $db = XenForo_Application::get('db');

        foreach (self::$_tables as $table) {
            $db->query($table['createQuery']);
        }

        foreach (self::$_patches as $patch) {
            $tableExisted = $db->fetchOne($patch['tableCheckQuery']);
            if (empty($tableExisted)) {
                continue;
            }

            $existed = $db->fetchOne($patch['checkQuery']);
            if (empty($existed)) {
                $db->query($patch['addQuery']);
            } elseif (!empty($patch['modifyQuery'])) {
                $db->query($patch['modifyQuery']);
            }
        }

        self::installCustomized($existingAddOn, $addOnData);
    }

    public static function uninstall()
    {
        $db = XenForo_Application::get('db');

        foreach (self::$_patches as $patch) {
            $tableExisted = $db->fetchOne($patch['tableCheckQuery']);
            if (empty($tableExisted)) {
                continue;
            }

            $existed = $db->fetchOne($patch['checkQuery']);
            if (!empty($existed)) {
                $db->query($patch['dropQuery']);
            }
        }

        foreach (self::$_tables as $table) {
            $db->query($table['dropQuery']);
        }

        self::uninstallCustomized();
    }

    /* End auto-generated lines of code. Feel free to make changes below */

    public static function installCustomized($existingAddOn, $addOnData)
    {
        // customized install script goes here
    }

    public static function uninstallCustomized()
    {
        // customized uninstall script goes here
    }
}
