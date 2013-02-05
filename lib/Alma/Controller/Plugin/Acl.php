<?php 
namespace Alma\Controller\Plugin;
/**
 * @author NMO <nico@multeegaming.com> 
 */
use ObjectValues\Role,
    Zend_Controller_Plugin_Abstract,
    Zend_Controller_Request_Abstract,
    Zend_Controller_Front,
    Zend_Session;
class Acl extends Zend_Controller_Plugin_Abstract
{

    /**
     * Route startup hook
     * 
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {


        $front = Zend_Controller_Front::getInstance();
        $bootstrap  = $front->getParam("bootstrap");
        $odm        = $bootstrap->getResource("odm");
        $events     = $bootstrap->getResource("events");
        $session    = $bootstrap->getResource("session");

        $redirectUri = false;
        $myUser = false;

//        $roleId   = Role::GUEST;
        if(!is_null($session->userId) && is_int($session->userId)) {

            // Get odm resource
            $myUser = $odm->getRepository('\Documents\Editor')->findOneBy(array(
                        'session_id' => Zend_Session::getId(),
                        'id' => $session->userId,
                        ));
            if(!$myUser) { 
                Zend_Session::destroy();
                // TODO: shall we notify user that we disconnected him explicitly
                $redirectUri = '/';
            }
        }

        if($redirectUri) {
            $r = new \Zend_Controller_Action_Helper_Redirector;
            $r->gotoUrl($redirectUri)->redirectAndExit();
        // Tracking & Banning system
        } else $events->trigger(__FUNCTION__, $this, array('odm' => $odm, 'myUser' => $myUser, 'request' => $request));
    }

}
