<?php

class bdPaygateNapas_bdPaygate_Model_Processor extends XFCP_bdPaygateNapas_bdPaygate_Model_Processor
{
    public function getCurrencies()
    {
        $currencies = parent::getCurrencies();

        $currencies[bdPaygateNapas_Processor_Napas::CURRENCY_VND] = 'VND';

        return $currencies;
    }

    public function formatAmount($amount, $currency)
    {
        if ($currency === bdPaygateNapas_Processor_Napas::CURRENCY_VND) {
            return XenForo_Locale::numberFormat($amount);
        }

        return parent::formatAmount($amount, $currency);
    }

    public function getProcessorNames()
    {
        $names = parent::getProcessorNames();
        $names['napas'] = 'bdPaygateNapas_Processor_Napas';
        return $names;
    }
}