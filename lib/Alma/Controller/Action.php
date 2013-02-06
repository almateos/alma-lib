<?php
/**
 * @author NMO <nico@multeegaming.com>
 */
namespace Alma\Controller;

use Zend_Controller_Action,
    \DateTime,
    \DateInterval,
    \Multee_Mail,
    DomainException;

abstract class Action extends \Zend_Controller_Action
{
    /** @var \Incube\Event\EventManager */
    protected $events;

    /** @var \Doctrine\ODM\MongoDB\DocumentManager */
    protected $odm;

    /** @var \Doctrine\ORM\EntityManager */
    protected $orm;

    /** @var \Documents\User|false */
    protected $currentUser = false;

    /** @var array */
    protected $options;

    public function init() {
        /** @var $bootstrap \Bootstrap */
        $bootstrap = $this->getFrontController()->getParam('bootstrap');
        $this->options = $bootstrap->getOptions();
        $resources = array_map('strtolower', array_keys($this->options["resources"]));
        foreach ($resources as $name) {
            if (!$bootstrap->hasResource($name)) throw new DomainException("Resources '$name' does not exist");
            $key = strtolower($name);
            if (!isset($this->$key)) $this->$key = $bootstrap->getResource($name);

        }
    }


    public function preDispatch() {
        $this->orm->lazyFlush = false;
        $this->odm->lazyFlush = false;
        $this->lazyRedirect = false;

        //if (!is_null($this->session->userId) && is_int($this->sessions->userId)) {
            //$this->view->currentUser = $this->currentUser = $this->odm->getRepository('\Documents\User')->find($this->sessions['identity']->userId);
        //}

        //$this->events->trigger(__FUNCTION__, $this, array('odm' => $this->odm));
    }

    public function postDispatch() {
        if ($this->orm->lazyFlush) $this->orm->flush();
        if ($this->odm->lazyFlush) $this->odm->flush();
        // Implement lazy redirect everywhere
        if ($this->lazyRedirect) $this->_redirect($this->lazyRedirect);
    }
}
