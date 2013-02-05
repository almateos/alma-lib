<?php
/**
 * @author NMO <nico@multeegaming.com> 
 */
class Alma_Validate_UserEmail extends Zend_Validate_Abstract {

	const EXIST = 'mailAlreadyExist';

	protected $_messageTemplates = array(
		self::EXIST => "Ce mail est déjà utilisé"
	);

    public function __construct($users = null, $user = null) {
        $this->_users = $users;
        $this->_user = $user;
    }

	public function isValid($value) {
        $result = true;
		$this->_setValue($value);

        if ($this->_user && $this->_user->getEmail() == $value) {
            return true;
        }

        if ($this->_users){

            list($name, $domain) = explode('@',$value);
            $name = explode('+',$name);
            $user = $name[0];

            $search = $this->_users->createQueryBuilder()
                ->hydrate(false)
                ->select('email')
                ->field('email')->equals(new \MongoRegex("/^$user(\+[\w\-_\.]*)?@$domain$/"))
                ->getQuery()
                ->getSingleResult();

            if ($search === null){
                $result = true;
            } else {
                $this->_error(self::EXIST);
                $result = false;
            }
        }
		return $result;
	}
}
