
<?php
/**
 * @author NMO <nico@multeegaming.com> 
 */
class Alma_Filter_FloatThenInt extends Alma_Filter_Float
{
    /** 
     * Defined by Zend_Filter_Interface
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {   
        $value = (float) parent::filter($value);
        return $value;
        //return (int) ($value * 100);
    }   
}
