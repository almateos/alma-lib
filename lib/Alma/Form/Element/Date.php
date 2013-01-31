<?php

class Alma_Form_Element_Date extends Zend_Form_Element_Text
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formDate';

	public function getValue(){
		$value = parent::getValue();
		return Alma_Date::anyToDate($value);
	}
}
