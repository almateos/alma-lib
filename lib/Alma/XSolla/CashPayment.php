<?php
namespace XSolla;

use SimpleXMLElement,
    Exception;
class CashPayment {

  //protected static $_whiteList = array('94.103.26.178', '94.103.26.181', '::1');
  protected static $_whiteList = array('94.103.26.178', '94.103.26.181', '::1', '94.23.243.223');

  protected static $_paramsSet = array(
          'pay'     => array('id', 'v1', 'currency', 'datetime', 'sign'),
          'cancel'  => array('id', 'md5')
          );
  protected static $_signatures = array(
        'pay'       => array('v1', 'amount', 'currency', 'id', 'sign'),
        'cancel'    => array('command', 'id', 'md5'),
          );

  protected $_error = false;

  protected $_fromIp;
  protected $_secretKey;

  public function __construct($fromIp, $secretKey, $params) { 
      $this->_fromIp = $fromIp;
      $this->_secretKey = $secretKey;
      $this->_params = $params;
  }

  public function isValidRequest() {
      return !$this->_error;
  }

  protected function _isValidRequest() {
      $params = $this->_params;
      if (in_array($this->_fromIp, self::$_whiteList)) {

          if (array_key_exists('command', $params)) {

              if(array_key_exists($params['command'], self::$_paramsSet)) {

                  $this->_command = $params['command'];
                  $responseParams = self::$_paramsSet[$params['command']];
                  $missingParams = self::_getMissingParams($responseParams, $params);
                  if (count($missingParams) === 0) {

                      if (self::_sign($params, $this->_secretKey)) {
                          $msg = false;

                      } else $msg = 'signatures does not match';
                  } else $msg = 'param "' . implode('", "', $missingParams).'" not included in request.';
              } else $msg = $params['command'] . ' is not an acceptable command.'; 
          } else $msg = 'param "command" was not included in request.';
      } else $msg = 'originating ip address not in IP whitelist.';
      //if($msg) throw new Exception($msg);
      if($msg) $this->_error = $msg;
  }

  protected function getParams() {
    $result = array();
    foreach(self::$_paramsSet[$params['command']] as $key) {
        $result[$key] = $this->_params[$key];
    }
    return $result;
  }

  protected static function _getMissingParams($paramList, $params) {
      $missingParams = array();
      foreach($paramList as $key) 
          if (!array_key_exists($key, $params))
              $missingParams[] = $key;  
      return $missingParams;
  }

  protected static function _sign($params, $secretKey) {
      $signaturesParams = self::$_signatures[$params['command']];
      $hashKey = array_pop($signaturesParams);
      $str = '';
      foreach($signaturesParams as $key)
          if(array_key_exists($key, $params)) $str .= $params[$key]; 

      return md5($str . $secretKey) === $params[$hashKey];
  }

  public static function createResponse($responseCode, $responseMsg, $params = array()) {
      $xml = new SimpleXMLElement('<response></response>');
      $xml->addChild('result', $responseCode);
      $xml->addChild('comment', $responseMsg);

      return $xml->asXML();
      //self::_arrayToXml($params, $xml);
      //header('Content-Type: text/xml; charset=cp1251');
      //echo html_entity_decode($xml->asXML(), ENT_COMPAT, 'windows-1251'); exit;
  }

  public static function createPayResponse($responseCode, $responseMsg, $params = array()) {
      $xml = new SimpleXMLElement('<response></response>');
      $xml->addChild('result', $responseCode);
      $xml->addChild('comment', $responseMsg);
      
      $fields = $xml->addChild('fields'); 
      $fields->addChild('id', $params['id']);
      $fields->addChild('store', $params['v1']);
      $fields->addChild('amount', $params['amount']);
      $fields->addChild('currency', $params['currency']);

      $now = new \DateTime();
      $datetime = $now->format('YmdHms');
      $fields->addChild('datetime', $datetime);

      $fields->addChild('sign', $params['sign']);

      return $xml->asXML();
      //header('Content-Type: text/xml; charset=cp1251');
      //echo html_entity_decode($xml->asXML(), ENT_COMPAT, 'windows-1251'); exit;
  }

    /**
     * @return mixed.
     */
    public function getError()
    {
        return $this->_error;
    }
    
    /**
     * @param mixed _error 
     * @return CashPayment
     */
    public function setError($_error)
    {
        $this->_error = $_error;
        return $this;
    }
}
