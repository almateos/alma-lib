<?php
namespace Alma;

use Zend_Mail,
    Zend_Mail_Transport_Smtp,
    Zend_Mail_Transport_File;

/**
 * @author NMO <nico@multeegaming.com> 
 */
class Mailer {

    protected $_transport;
    protected $_config;

    /** @param array $config */
	public function __construct(array $config) {
        $this->_config = $config;
        if($config['transport']['type'] === 'smtp') {
            $this->_transport = new Zend_Mail_Transport_Smtp($config['transport']['host'], $config['transport']['config']);

        } elseif($config['transport']['type'] === 'file') {
            if(!file_exists($config['transport']['options']['path']))
                if(!mkdir($config['transport']['options']['path']))
                    trigger_error('"' . $config['transport']['options']['path'] . '" is not a valid file');
            $this->_transport = new Zend_Mail_Transport_File($config['transport']['options']);

        } else trigger_error('unsuported type of transport: "' . $config['transport']['type'] . '"');

        if(isset($config['defaults'])){
            if(isset($config['defaults']['from'])) Zend_Mail::setDefaultFrom($config['defaults']['from']['email'], $config['defaults']['from']['name']);
            if(isset($config['defaults']['reply-to'])) Zend_Mail::setDefaultReplyTo($config['defaults']['reply-to']['email'], $config['defaults']['reply-to']['name']);
        }
	}

    /**
     * @param $title
     * @param $message
     * @param array $to
     * @param array $from
     */
    public function sendTextMail($title, $message, array $to = array(), array $from = array()) {
        $mail = new Zend_Mail('utf-8');
        $mail->setSubject($title);
        $mail->setBodyHtml($message);
        $mail->addTo($to['email'], @$to['name'] ? $to['name'] : null);
        if(isset($from['email']) && $from['email']) $mail->setFrom($from['email'], @$from['name'] ? $from['name'] : null );
        $mail->send($this->_transport);
	}

    /**
     * @param $title
     * @param $templatePath
     * @param array $templateVars
     * @param array $to
     * @param array $from
     */
    public function sendTemplateMail($title, $templatePath, array $templateVars = array(), array $to = array(), array $from = array()) {
        $file = $this->_config['template_folder'] . '/' . $templatePath;
        if(file_exists($file)){
            $message = file_get_contents($file);
            if (count($templateVars) > 0) {
                foreach ($templateVars as $key => $value) {
                    $message = preg_replace('%' . $key . '%', $value, $message);
                }
            }
            $this->sendTextMail($title, $message, $to, $from);
        }else{
            trigger_error('mail template '.$file.' not found');
        }
    }
}
