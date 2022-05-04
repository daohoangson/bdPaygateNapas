<?php

class bdPaygateNapas_Processor_Napas extends bdPaygate_Processor_Abstract
{
    const CURRENCY_VND = 'vnd';

    public function getSupportedCurrencies()
    {
        return [self::CURRENCY_VND];
    }

    public function isRecurringSupported()
    {
        return false;
    }

    public function validateCallback(Zend_Controller_Request_Http $request, &$transactionId, &$paymentStatus, &$transactionDetails, &$itemId)
    {
        $amount = false;
        $currency = false;

        return $this->validateCallback2($request, $transactionId, $paymentStatus, $transactionDetails, $itemId, $amount, $currency);
    }


    public function validateCallback2(Zend_Controller_Request_Http $request, &$transactionId, &$paymentStatus, &$transactionDetails, &$itemId, &$amount, &$currency)
    {
        return $this->validateCallbackNapas(
            $request->getRawBody(),
            $transactionId,
            $paymentStatus,
            $transactionDetails,
            $itemId,
            $amount,
            $currency
        );
    }

    /**
     * @param string $rawNapasResult
     * @param string $transactionId
     * @param string $paymentStatus
     * @param array $transactionDetails
     * @param string $itemId
     * @param string $amount
     * @param string $currency
     *
     * @return bool
     */
    public function validateCallbackNapas($rawNapasResult, &$transactionId, &$paymentStatus, &$transactionDetails, &$itemId, &$amount, &$currency)
    {
        $napasResult = json_decode($rawNapasResult, true);
        if (!is_array($napasResult)) {
            $transactionDetails['rawNapasResult'] = $rawNapasResult;
            $this->_setError('Invalid NAPAS result: is not JSON');
            return false;
        }
        if (!isset($napasResult['data'])) {
            $transactionDetails['napasResult'] = $napasResult;
            $this->_setError('Invalid NAPAS result: does not contain `data`');
            return false;
        }
        if (!isset($napasResult['checksum'])) {
            $transactionDetails['napasResult'] = $napasResult;
            $this->_setError('Invalid NAPAS result: does not contain `checksum`');
            return false;
        }

        $rawData = $napasResult['data'];
        $checksumActual = $napasResult['checksum'];
        $checksumExpected = bdPaygateNapas_Helper_Api::checksum($rawData);
        if ($checksumActual !== $checksumExpected) {
            $transactionDetails['rawData'] = $rawData;
            $transactionDetails['checksumActual'] = $checksumActual;
            $transactionDetails['checksumExpected'] = $checksumExpected;
            $this->_setError('Invalid NAPAS result: checksum does not match');
            return false;
        }

        $b64DecodedData = base64_decode($rawData);
        $data = json_decode($b64DecodedData, true);
        if (!is_array($data)) {
            $transactionDetails['rawData'] = $rawData;
            $this->_setError('Invalid NAPAS result: is not base64 encoded');
            return false;
        }
        $transactionDetails['data'] = $data;
        if (!isset($data['paymentResult'])) {
            $this->_setError('Invalid NAPAS data: does not contain `paymentResult`');
            return false;
        }

        $paymentResult = $data['paymentResult'];
        $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_ERROR;
        if (isset($paymentResult['apiOperation'])) {
            switch ($paymentResult['apiOperation']) {
                case 'PAY':
                case 'PURCHASE':
                    if ($paymentResult['result'] === 'SUCCESS') {
                        $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_ACCEPTED;
                    } else {
                        $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_OTHER;
                    }
                    break;
                case 'REFUND':
                case 'REFUND_DOMESTIC':
                    if ($paymentResult['result'] === 'SUCCESS') {
                        $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_REJECTED;
                    } else {
                        $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_OTHER;
                    }
                    break;
            }
        }

        if (isset($paymentResult['order'])) {
            $order = $paymentResult['order'];
            if (isset($order['id'])) {
                $napasOrderId = $order['id'];
                $transactionDetails['napasOrderId'] = $napasOrderId;

                /** @var bdPaygateNapas_Model_Order $orderModel */
                $orderModel = XenForo_Model::create('bdPaygateNapas_Model_Order');
                $order = $orderModel->getOrderById($napasOrderId);

                if (!empty($order)) {
                    $transactionDetails['_order'] = $order;

                    $itemId = $order['item_id'];
                    $transactionDetails['item_id'] = $itemId;
                }
            }
        }

        if (isset($paymentResult['transaction'])) {
            $transaction = $paymentResult['transaction'];
            if (isset($transaction['id'])) {
                $transactionId = $transaction['id'];
            }

            if (isset($transaction['amount'])) {
                $amount = $transaction['amount'];
                $transactionDetails['amount'] = $amount;
            }
            if (isset($transaction['currency'])) {
                $currency = $transaction['currency'];
                $transactionDetails['currency'] = $currency;
            }
        }

        return true;
    }

    /**
     * @throws XenForo_Exception
     */
    public function generateFormData($amount, $currency, $itemName, $itemId, $recurringInterval = false, $recurringUnit = false, array $extraData = array())
    {
        $this->_assertAmount($amount);
        $this->_assertCurrency($currency);
        $this->_assertItem($itemName, $itemId);

        $templateParams = [
            'action' => XenForo_Link::buildPublicLink('napas'),
            'cardSchemes' => [
                bdPaygateNapas_Helper_Api::CARD_SCHEME_ATM_CARD => new XenForo_Phrase('bdpaygatenapas_card_scheme_atm_card'),
                bdPaygateNapas_Helper_Api::CARD_SCHEME_CREDIT_CARD => new XenForo_Phrase('bdpaygatenapas_card_scheme_credit_card'),
            ],
        ];

        $data = json_encode([
            'amount' => $amount,
            'currency' => $currency,
            'item_id' => $itemId,
            'item_name' => $itemName,

            'return_url' => $this->_generateReturnUrl($extraData),
        ]);
        $encrypted = bdPaygateNapas_ShippableHelper_Crypt::encrypt($data);
        $templateParams['encrypted'] = base64_encode($encrypted);

        $fc = XenForo_Application::getFc();
        return $fc->getDependencies()->createTemplateObject('bdpaygatenapas_form', $templateParams);
    }
}