<?php
/**
 * @author NMO <nico@multeegaming.com> 
 */
class Alma_Filter_CommaSeparatedValues implements Zend_Filter_Interface
{
    /** 
     * Defined by Zend_Filter_Interface
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $tokens = array_map('trim', explode(',', $value));
        $tokens = array_filter(array_unique($tokens));
        return implode(',',$tokens);
    }   
}
