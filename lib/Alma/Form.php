<?php
 
class Alma_Form extends Zend_Form{

    protected static $_fieldMap = array(
                'boolean'   => 'checkbox',

                'integer'   => 'text',
                'int'       => 'text',

                'date'      => 'text',

                'double'    => 'text',
                'float'     => 'text',
                'string'    => 'text',

                'array'     => 'select',
                'collection'=> 'select',
                );

    protected static $_formClass = 'Alma_Form_SubForm';

    public static function setFormClass($className) {
        self::$_formClass = $className;
    }

    public static function getFieldMap() {
        return self::$_fieldMap;    
    }

    public static function getFieldForType($type) {
        return self::$_fieldMap[$type];
    }

    /** Experimental function used in admin to generate dynamically form from data
     * @param array $data 
     * @return Alma_Form 
     */
    public static function getFormConfigFromData(array $data) {

        $dynamicElements = array();
        foreach($data as $key => $datum) {
            list($type, $options) = self::_getConfigFromValue($datum);
            $options['label'] = $key;
            $dynamicElements[$key] = array(
                    'element' => $type, 
                    'name' => $key, 
                    'options' =>$options
                    );
        }
        return $dynamicElements;
    }

    /** Experimental function used in admin to generate dynamically form from data
     * @param array $data 
     * @return Alma_Form 
     */
    public static function generateFromArray(array $data, $options = array()) {

        $formClass = array_key_exists('formClass', $options) ? $options['formClass'] : self::$_formClass;
        $form = new $formClass();
        foreach($data as $key => $datum) {
            call_user_func_array(array($form, 'addElement'), $datum);

            $optKey = in_array($datum['element'], array("radio", "select")) ? 'multiOptions' :'value';
            if($optKey === 'multiOptions' && is_null($datum['options']['value'])) $datum['options']['value'] = array();
            $option[$optKey] = $datum;
            $element = $form->getElement($datum['name']);
            if(array_key_exists('value', $datum['options'])) $element->{'set' . ucfirst($optKey)}($datum['options']['value']);


            //$form->getElement($datum['name'])->setValue($datum['options']['value']);
        }
        if(!array_key_exists('submitBtn', $options) || $options['submitBtn']) $form->addElement('submit', 'submit', array(
			'id' => 'submitbutton',
            'class' => 'btn'
		));
        return $form;
    }

	/** identify type of a variable and return appropriate form type 
	  *
	  * @param mixed $values
	  * @return string */
    protected function _getConfigFromValue($value) {
        $validators = array();
        $filters = array();
        $fieldType = gettype($value);
        $validators = array();
        if($fieldType === 'boolean') {
            //$type = "checkbox";
            //$validators[] = 'boolean';
            $filters[] = 'boolean';
        } elseif($fieldType === 'float') {
            $validators[] = new Zend_Validate_Float(array('locale' => 'en'));
            $filters[] = new Alma_Filter_Float();
        } elseif($fieldType === 'integer') { 
            $validators[] = 'int';
            $filters[] = 'int';
        } elseif($fieldType === 'double') { 
            $validators[] = new Zend_Validate_Float(array('locale' => 'en'));
            //$validators[] = 'digits';
            $filters[] = new Alma_Filter_Double();
        }
        //elseif(is_string($values))  $type = "text";
        //elseif(is_array($values))   $type = "select";
        //else trigger_error("unidentified type of data, can't dynamically create this element", E_USER_ERROR);

        $options = array(
                'validators' => $validators,
                'filters' => $filters,
                'value' => $value,
                );
        return array(self::$_fieldMap[$fieldType], $options);
    }

}
