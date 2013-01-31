<?php
/**
 * @author NMO <nico@multeegaming.com> 
 */
class Alma_Filter_Float implements Zend_Filter_Interface
{
    /** 
     * Defined by Zend_Filter_Interface
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {   
        return str_replace(",", ".", $value);
    }   
}
