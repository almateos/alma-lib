<?php

class Alma_Form_Element_DateTime extends Zend_Form_Element_Text
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formDateTime';

	public function getValue(){
		$value = parent::getValue();
		return \Alma\Date::anyToDateTime($value);
	}
}
