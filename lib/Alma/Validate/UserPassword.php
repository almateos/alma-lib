<?php

class Alma_Validate_UserPassword extends Zend_Validate_Abstract {

	const LENGTH_MIN = 'lengthMin';
	const LENGTH_MAX = 'lengthMax';
	const REGEX = 'regex';

	protected $_messageTemplates = array(
		self::LENGTH_MIN => "doit comporter au moins 6 caractères",
		self::LENGTH_MAX => "doit comporter moins de 20 caractères",
		self::REGEX => "les caractères spéciaux ne sont pas autorisés"
	);

	public function isValid($value) {
		$this->_setValue($value);

		if (strlen($value) < 6) {
			$this->_error(self::LENGTH_MIN);
			return false;
		}

		if (strlen($value) > 20) {
			$this->_error(self::LENGTH_MAX);
			return false;
		}

		$regexValidator = new Zend_Validate_Regex(array('pattern' => '/^[A-Za-z0-9À-ÿ._-]{6,20}$/'));
		if (!$regexValidator->isValid($value)) {
			$this->_error(self::REGEX);
			return false;
		}

		return true;
	}
}
