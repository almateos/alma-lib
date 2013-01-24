<?php
namespace Alma;

use Zend_Mail,
    Zend_Mail_Transport_Smtp,
    Zend_Mail_Transport_File;

/**
 * @author NMO <nico@multeegaming.com> 
 */
class Mailer extends Zend_Mail {

    protected $_transport;
    protected $_config;

    /** @param array $config */
	public function __construct(array $config) {
		parent::__construct('utf-8');
        $this->_config = $config;
        if($config['transport']['type'] === 'smtp') {
            $this->_transport = new Zend_Mail_Transport_Smtp($config['transport']['host'], $config['transport']['config']);

        } elseif($config['transport']['type'] === 'file') {
            if(!file_exists($config['transport']['options']['path']))
                if(!mkdir($config['transport']['options']['path']))
                    trigger_error('"' . $config['transport']['options']['path'] . '" is not a valid file');
            $this->_transport = new Zend_Mail_Transport_File($config['transport']['options']);

        } else trigger_error('unsuported type of transport: "' . $config['transport']['type'] . '"');
	}

    /**
     * @param string $title 
     * @param string $message 
     * @param string $to 
     * @param string $pseudo 
     */
	public function sendTextMail($title, $message, $to, $pseudo) {
		$this->setSubject($title);
		$this->setBodyHtml($message);
		$this->addTo($to, $pseudo);

        $this->send($this->_transport);
	}

    /**
     * @param string $title 
     * @param string $templatePath 
     * @param array $changes 
     */
    public function sendTemplateMail($title, $templatePath, array $changes = array()) {
        if(!file_exists($templatePath)) {
            $file = $this->_config['template_folder'] . '/' . $templatePath;
            $message = file_get_contents($file);
            if (count($changes) > 0) {
                foreach ($changes as $key => $value) {
                    $message = preg_replace('#' . $key . '#', $value, $message);
                }
            }
            $this->sendText($title, $message, $to, $pseudo, $from);
        }
    }
}
