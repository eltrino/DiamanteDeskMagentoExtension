<?php

/**
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
class Eltrino_DiamanteDesk_Model_Api extends Mage_Core_Model_Abstract
{
    const API_URL_POSTFIX = '/api/rest/v1/';

    const API_RESPONSE_FORMAT = 'json';

    const TYPE_ORO_USER = 'oro_';

    const TYPE_DIAMANTE_USER = 'diamante_';

    /** @var Zend_Http_Client */
    protected $_http;

    /** @var Zend_Http_Response */
    public $result;

    /** @var array */
    protected $config = array();

    /** @var array */
    protected $allowedStatuses = array(200, 201, 202, 204, 304);

    /** @var array */
    protected $_filters = array();

    /**
     * @return $this
     * @throws Zend_Http_Client_Exception
     */
    public function init()
    {
        $this->initConfig();

        $this->_http = new Zend_Http_Client();

        $this->_http->setAdapter(new Zend_Http_Client_Adapter_Curl);

        $this->_http->setHeaders(
            array(
                'Accept'        => 'application/' . static::API_RESPONSE_FORMAT,
                'Authorization' => 'WSSE profile="UsernameToken"',
                'X-WSSE'        => $this->_getWsseHeader()
            )
        );

        return $this;
    }

    /**
     * @param null $userName
     * @param null $apiKey
     * @param null $serverAddress
     * @return $this
     */
    public function initConfig($userName = null, $apiKey = null, $serverAddress = null)
    {
        /** Check is config already initialized */
        if (count($this->config)) return $this;


        $this->config['userName'] = $userName ? $userName : Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_USERNAME);
        $this->config['apiKey'] = $apiKey ? $apiKey : Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_KEY);
        $this->config['serverAddress'] = $serverAddress ? $serverAddress : Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_SERVER_ADDRESS);

        return $this;
    }

    /**
     * @return string
     */
    protected function _getWsseHeader()
    {
        $nonce = Mage::helper('core')->getRandomString(10);
        $created = new DateTime('now', new DateTimezone('UTC'));
        $created = $created->format(DateTime::ISO8601);
        $digest = sha1($nonce . $created . $this->config['apiKey'], true);

        return sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $this->config['userName'],
            base64_encode($digest),
            base64_encode($nonce),
            $created
        );
    }

    /**
     * @param $method
     * @return $this
     * @throws Zend_Http_Client_Exception
     */
    public function setHttpMethod($method)
    {
        $this->_http->setMethod($method);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addPostData(array $data)
    {
        foreach ($data as $key => $param) {
            $this->_http->setParameterPost($key, $param);
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addFilter($key, $value)
    {
        $this->_filters[$key] = $value;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        try {
            $this->_http->setUri(trim($this->config['serverAddress'], '/') . static::API_URL_POSTFIX . $method . '.' . static::API_RESPONSE_FORMAT);
        } catch (Exception $e) {
            /** Exception will be thrown later */
        }
        return $this;
    }

    /**
     * @param bool $checkForErrors
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function doRequest($checkForErrors = true)
    {
        $helper = Mage::helper('core/translate');

        $this->_applyFilters();

        if (!$this->_http->getUri()) {
            Mage::throwException($helper->__('Undefined request method'));
        }

        try {
            $this->result = $this->_http->request();
        } catch (Exception $e) {
            Mage::throwException('Can\'t process request');
        }

        if (!in_array($this->result->getStatus(), $this->allowedStatuses) && $checkForErrors) {
            $body = json_decode($this->result->getBody());
            Mage::throwException($body->error ? $body->error : $this->result->getMessage());
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Zend_Http_Client_Exception
     */
    protected function _applyFilters()
    {
        if (empty($this->_filters)) {
            return $this;
        }

        $uri = $this->_http->getUri(true);

        foreach ($this->_filters as $key => $value) {
            /** is some filters already applied */
            if (!strpos($uri, '?')) {
                $uri .= '?';
            } else {
                $uri .= '&';
            }
            $uri .= $key . '=' . $value;
        }

        $this->_http->setUri($uri);

        return $this;
    }

    /**
     * @return bool|mixed|string
     */
    public function getBranches()
    {
        if ($branches = $this->getData('branches')) {
            return $branches;
        }

        try {
            $this->init()->setMethod('desk/branches')->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        $this->setBranches($this->result->getBody());

        return $this->result->getBody();
    }

    /**
     * @param bool $useCache
     * @return bool|mixed|string
     */
    public function getTickets($useCache = true)
    {
        if ($useCache && $tickets = $this->getData('tickets')) {
            return $tickets;
        }

        try {
            $this->init()->setMethod('desk/tickets')->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        if ($useCache) {
            $this->setTickets($this->result->getBody());
        }

        return $this->result->getBody();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function getTicket($key)
    {
        try {
            $this->init()->setMethod('desk/tickets/' . $key)->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        return json_decode($this->result->getBody());
    }

    /**
     * @param array $data
     * @return bool
     */
    public function createTicket($data)
    {
//        if (!isset($data['assignee']) || $data['assignee'] == null) {
//            $branches = json_decode($this->getBranches());
//            foreach ($branches as $branch) {
//                if ($branch->id == (int)$data['branch']) {
//                    $data['assignee'] = $branch->default_assignee;
//                }
//            }
//        }

        if (is_numeric($data['reporter'])) {
            if (Mage::app()->getStore()->isAdmin()) {
                $data['reporter'] = static::TYPE_ORO_USER . $data['reporter'];
            } else {
                $data['reporter'] = static::TYPE_DIAMANTE_USER . $data['reporter'];
            }
        }


        try {
            $this->init()
                ->setMethod('desk/tickets')
                ->setHttpMethod('POST')
                ->addPostData($data)
                ->doRequest();
        } catch (Exception $e) {
            return false;
        }

        $result = json_decode($this->result->getBody());

        if (isset($data['order_increment_id'])) {
            Mage::getModel('eltrino_diamantedesk/orderRelation')->saveRelation($result->id, $data['order_increment_id']);
        }

        return true;
    }


    /**
     * data = ['ticket_id','filename','content']
     *
     * @param $data
     * @return bool
     */
    public function addAttachmentToTicket($data)
    {
        try {
            $ticketId = $data['ticket_id'];
            unset($data['ticket_id']);
            $this->init()
                ->setMethod('desk/tickets/' . $ticketId . '/attachments')
                ->setHttpMethod('POST')
                ->addPostData(
                    array(
                        'attachmentsInput' => array($data)
                    )
                )
                ->doRequest();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return bool|mixed|string
     */
    public function getUsers()
    {
        if ($users = $this->getData('users')) {
            return $users;
        }

        try {
            $this->init()->setMethod('users')->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        $this->setUsers($this->result->getBody());

        return $this->result->getBody();
    }

    /**
     * @param $key
     * @param $value
     * @param bool $useCache
     * @return mixed
     */
    public function getUserByFilter($key, $value, $useCache = true)
    {
        /** Users cache mechanism */
        if ($useCache) {
            if ($users = $this->getData('users')) {
                if (isset($users[$key . $value])) {
                    return $users[$key . $value];
                }
            } else {
                $this->setData('users', array());
            }
        }

        try {
            $this->init()
                ->setMethod('user/filter')
                ->addFilter($key, $value)
                ->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        if ($this->result->getStatus() == 404) {
            return false;
        }

        $user = json_decode($this->result->getBody());

        /** save to cache received user data*/
        if ($useCache) {
            $users = $this->getData('users');
            $users[$key . $value] = $user;
            $this->setData('users', $users);
        }

        return $user;
    }

    /**
     * @param $email
     * @return bool|mixed
     */
    public function getDiamanteUserByEmail($email)
    {
        try {
            $this->init()
                ->setMethod('desk/users/' . $email . '/')
                ->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        if ($this->result->getStatus() !== 404) {
            return json_decode($this->result->getBody());
        }

        return false;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function getDiamanteUserById($id)
    {
        try {
            $this->init()
                ->setMethod('desk/users/' . $id)
                ->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        if ($this->result->getStatus() !== 404) {
            return json_decode($this->result->getBody());
        }

        return false;
    }


    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return bool|mixed
     */
    public function createDiamanteUser(Mage_Customer_Model_Customer $customer)
    {
        try {
            $this->init()
                ->setMethod('desk/users')
                ->setHttpMethod('POST')
                ->addPostData(array(
                    'email'     => $customer->getEmail(),
                    'firstName' => $customer->getFirstname(),
                    'lastName'  => $customer->getLastname(),
                ))->doRequest(false);
        } catch (Exception $e) {
            return false;
        }

        if ($this->result->getStatus() === 201) {
            $diamanteUser = json_decode($this->result->getBody());
            $customerRelation = Mage::getModel('eltrino_diamantedesk/customerRelation');
            $customerRelation->setData(
                array(
                    'customer_id' => $customer->getId(),
                    'user_id'     => $diamanteUser->id
                )
            );
            $customerRelation->save();
            return $diamanteUser;
        }

        return false;
    }

    /**
     * @param $customer Mage_Customer_Model_Customer
     * @return bool|mixed
     * @throws Mage_Core_Exception
     */
    public function getOrCreateDiamanteUser($customer)
    {
        $customerRelation = Mage::getModel('eltrino_diamantedesk/customerRelation');
        $customerRelation->load($customer->getId(), 'customer_id');

        if ($customerRelation->getId()) {
            $result = $this->getDiamanteUserById($customerRelation->getUserId());
            if ($result) {
                return $result;
            }
        }

        $diamanteUser = $this->getDiamanteUserByEmail($customer->getEmail());
        if ($diamanteUser) {
            $customerRelation->addData(
                array(
                    'customer_id' => $customer->getId(),
                    'user_id'     => $diamanteUser->id,
                )
            );
            $customerRelation->save();
            return $diamanteUser;
        }

        return $this->createDiamanteUser($customer);
    }

    /**
     * @return array
     */
    public function getDiamanteUsers()
    {
        try {
            $this->init()
                ->setMethod('desk/users')
                ->doRequest(false);
        } catch (Exception $e) {
            Mage::logException($e);
            return array();
        }

        if ($this->result->getStatus() == 200) {
            return json_decode($this->result->getBody());
        }

        return array();
    }

    /**
     * @param array $data
     */
    public function createComment(array $data)
    {
        if (is_numeric($data['author'])) {
            if (Mage::app()->getStore()->isAdmin()) {
                $data['author'] = static::TYPE_ORO_USER . $data['author'];
            } else {
                $data['author'] = static::TYPE_DIAMANTE_USER . $data['author'];
            }
        }

        try {
            $this->init()
                ->setHttpMethod('POST')
                ->setMethod('desk/comments')
                ->addPostData($data)
                ->doRequest();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @param $ticketId
     * @return array|mixed
     */
    public function listComments($ticketId)
    {
        try {
            $this->init()
                ->setMethod('desk/comments')
                ->addFilter('ticket', $ticketId)
                ->doRequest();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if ($this->result->getStatus() == 200) {
            return json_decode($this->result->getBody());
        }

        return array();
    }

    /**
     * TODO: Data should be retrieve from ticket
     * @param $authorId
     * @param $authorType
     */
    public function getCommentAuthor($authorId, $authorType)
    {

    }
}
