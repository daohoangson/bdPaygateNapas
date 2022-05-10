<?php

class bdPaygateNapas_ControllerPublic_Napas extends XenForo_ControllerPublic_Abstract
{
    /**
     * @throws XenForo_Exception
     * @throws Zend_Http_Client_Exception
     * @throws XenForo_ControllerResponse_Exception
     */
    public function actionForm()
    {
        $this->_assertPostOnly();

        $input = $this->_input->filter(array(
            'card_scheme' => XenForo_Input::STRING,
            'encrypted' => XenForo_Input::STRING,
        ));

        $decoded = base64_decode($input['encrypted']);
        $decrypted = bdPaygateNapas_ShippableHelper_Crypt::decrypt($decoded);
        $processorData = json_decode($decrypted, true);
        if (!is_array($processorData)) {
            return $this->responseNoPermission();
        }

        $dw = XenForo_DataWriter::create('bdPaygateNapas_DataWriter_Order');
        $dw->set('item_id', $processorData['item_id']);
        $dw->set('return_url', $processorData['return_url']);
        $dw->save();

        $dataKey = bdPaygateNapas_Helper_Api::getDataKey(
            $dw->get('order_id'),
            $processorData['amount'],
            $processorData['currency'],
            $processorData['item_name'],
            $input['card_scheme']
        );

        $viewParams = [
            'dataKey' => $dataKey,
        ];

        return $this->responseView(
            'bdPaygateNapas_ViewPublic_Napas_PaymentPage',
            'bdpaygatenapas_payment_page',
            $viewParams
        );
    }

    /**
     * @throws XenForo_ControllerResponse_Exception
     * @throws XenForo_Exception
     */
    public function actionCallback()
    {
        $this->_assertPostOnly();

        /** @var bdPaygateNapas_Processor_Napas $processor */
        $processor = bdPaygate_Processor_Abstract::create('bdPaygateNapas_Processor_Napas');

        $transactionId = false;
        /** @var string|false $paymentStatus */
        $paymentStatus = false;
        $transactionDetails = [];
        $itemId = false;
        $amount = false;
        $currency = false;

        $rawNapasResult = $this->_input->filterSingle('napasResult', XenForo_Input::STRING);
        $validateResult = $processor->validateCallbackNapas(
            $rawNapasResult,
            $transactionId,
            $paymentStatus,
            $transactionDetails,
            $itemId,
            $amount,
            $currency
        );

        $returnUrl = $this->_buildLink('canonical:index');
        if (isset($transactionDetails['_order'])) {
            $order = $transactionDetails['_order'];
            if (isset($order['return_url'])) {
                $returnUrl = $order['return_url'];
            }
        }

        if ($validateResult) {
            if ($paymentStatus !== bdPaygate_Processor_Abstract::PAYMENT_STATUS_ACCEPTED) {
                return $this->responseError(new XenForo_Phrase('bdpaygatenapas_payment_status_not_accepted'));
            }
        }

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED, $returnUrl);
    }

    protected function _checkCsrf($action)
    {
        if (strtolower($action) === 'callback') {
            return;
        }

        parent::_checkCsrf($action);
    }
}
