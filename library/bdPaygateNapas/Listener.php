<?php

class bdPaygateNapas_Listener
{
    public static function load_class_bdPaygate_Model_Processor($class, array &$extend)
    {
        if ($class === 'bdPaygate_Model_Processor') {
            $extend[] = 'bdPaygateNapas_bdPaygate_Model_Processor';
        }
    }

    public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += bdPaygateNapas_FileSums::getHashes();
    }
}
