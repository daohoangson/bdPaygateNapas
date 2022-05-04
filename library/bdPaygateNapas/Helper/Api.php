<?php

class bdPaygateNapas_Helper_Api
{
    const API_ROOT_PRODUCTION = 'https://dps.napas.com.vn/api';
    const API_ROOT_STAGING = 'https://dps-staging.napas.com.vn/api';

    const API_DATA_KEY_FORMAT = 'rest/version/32/merchant/%s/datakey';
    const API_OAUTH_TOKEN = 'oauth/token';

    const CARD_SCHEME_CREDIT_CARD = 'CreditCard';
    const CARD_SCHEME_ATM_CARD = 'AtmCard';

    /**
     * @throws XenForo_Exception
     * @throws Zend_Http_Client_Exception
     */
    public static function getAccessToken()
    {
        $uri = sprintf('%s/%s', self::_apiRoot(), self::API_OAUTH_TOKEN);
        $client = XenForo_Helper_Http::getClient($uri);
        $client->setParameterPost([
            'grant_type' => 'password',
            'client_id' => self::_clientId(),
            'client_secret' => self::_clientSecret(),
            'username' => self::_credentialsUsername(),
            'password' => self::_credentialsPassword(),
        ]);

        $response = $client->request('POST');
        $responseStatus = $response->getStatus();

        if ($response->getStatus() !== 200) {
            throw new XenForo_Exception(sprintf('%s: unexpected status %d', __METHOD__, $responseStatus));
        }

        $responseBody = $response->getBody();
        $json = json_decode($responseBody, true);
        if (!is_array($json) || !isset($json['access_token'])) {
            throw new XenForo_Exception(sprintf('%s: unexpected response %s', __METHOD__, $responseBody));
        }

        return $json['access_token'];
    }

    /**
     * @throws Zend_Http_Client_Exception
     * @throws XenForo_Exception
     */
    public static function getDataKey($orderId, $orderAmount, $orderCurrency, $orderReference, $cardScheme)
    {
        $apiRoot = self::_apiRoot();
        $uriFormat = sprintf('%s/%s', $apiRoot, self::API_DATA_KEY_FORMAT);
        $uri = sprintf($uriFormat, self::_clientId());
        $client = XenForo_Helper_Http::getClient($uri);

        $accessToken = self::getAccessToken();
        $client->setHeaders('Authorization', sprintf('Bearer %s', $accessToken));

        $client->setHeaders('Content-Type', 'application/json');
        $reqData = [
            'apiOperation' => 'DATA_KEY',
            'order' => [
                'id' => $orderId,
                'amount' => intval($orderAmount),
                'currency' => strtoupper($orderCurrency),
            ],
            'inputParameters' => [
                'clientIP' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                'deviceId' => XenForo_Application::getSession()->getSessionId(),
                'environment' => 'WebApp',
                'cardScheme' => $cardScheme,
                'enable3DSecure' => $cardScheme == self::CARD_SCHEME_CREDIT_CARD,
            ],
        ];
        $client->setRawData(json_encode($reqData));

        $response = $client->request('POST');
        $responseStatus = $response->getStatus();

        if ($response->getStatus() !== 200) {
            throw new XenForo_Exception(sprintf('%s: unexpected status %d', __METHOD__, $responseStatus));
        }

        $responseBody = $response->getBody();
        $json = json_decode($responseBody, true);
        if (!is_array($json) || !isset($json['result']) || $json['result'] !== 'SUCCESS') {
            throw new XenForo_Exception(sprintf('%s: unexpected response %s', __METHOD__, $responseBody));
        }

        return array_merge(
            $reqData['inputParameters'],
            [
                'apiRoot' => $apiRoot,
                'clientId' => self::_clientId(),
                'clientChannel' => self::_clientChannel(),
                'order' => array_merge($reqData['order'], ['reference' => $orderReference]),
                'dataKey' => $json['dataKey'],
                'napasKey' => $json['napasKey'],
            ]
        );
    }

    public static function checksum($data)
    {
        $hash = hash('sha256', $data . self::_clientSecret());
        return strtoupper($hash);
    }

    private static function _apiRoot()
    {
        if (self::_getOption('isProduction')) {
            return self::API_ROOT_PRODUCTION;
        } else {
            return self::API_ROOT_STAGING;
        }
    }

    private static function _clientChannel()
    {
        return self::_getOption('clientChannel');
    }

    private static function _clientId()
    {
        return self::_getOption('clientId');
    }

    private static function _clientSecret()
    {
        return self::_getOption('clientSecret');
    }

    private static function _credentialsUsername()
    {
        return self::_getOption('username');
    }

    private static function _credentialsPassword()
    {
        return self::_getOption('password');
    }

    private static function _getOption($key)
    {
        $options = XenForo_Application::getOptions();
        return $options->get(sprintf('bdPaygateNapas_%s', $key));
    }
}