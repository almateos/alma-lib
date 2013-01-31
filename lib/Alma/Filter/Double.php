<?php
class Alma_Filter_Double implements Zend_Filter_Interface
{
    /** 
     * Returns (double) $value
     *
     * @param  string $value
     * @return double
     */
    public function filter($value)
    {   
        return (double) ((string) $value);
    }   
}
