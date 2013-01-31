<?php
namespace Alma;

use DateTime;

class Date
{
     static protected $DateFormat = 'd/m/Y';

     static protected $DateTimeFormat = 'd/m/Y H:i:s';

    /**
     * @static
     * @throws InvalidArgumentException
     * @param null|int|string|MongoDate|DateTime $date
     * @return DateTime|null
     */
    static function anyToDateTime($date = null){
        if(empty($date)) return null;
        if($date instanceof DateTime) return $date;

        $d = new DateTime();
        if($date instanceof MongoDate) return $d->setTimestamp($date->sec);
        if(is_int($date)) return $d->setTimestamp($date);
        if(is_string($date)){
            $d = DateTime::createFromFormat(self::$DateTimeFormat, $date);
            if($d instanceof DateTime) return $d;
        }
        throw new InvalidArgumentException('Date formatting not recognized : '.var_export($date, true));
    }

    /**
     * @static
     * @throws InvalidArgumentException
     * @param null|int|string|MongoDate|DateTime $date
     * @return DateTime|null
     */
    static function anyToDate($date = null){
        if(empty($date)) return null;
        if($date instanceof DateTime) return $date;

        $d = new DateTime();
        if($date instanceof MongoDate) return $d->setTimestamp($date->sec);
        if(is_int($date)) return $d->setTimestamp($date);
        if(is_string($date)){
            $d = DateTime::createFromFormat(self::$DateFormat, $date);
            if($d instanceof DateTime) return $d;
        }
        throw new InvalidArgumentException('Date formatting not recognized : '.var_export($date, true));
    }

    /**
     * @static
     * @param int|DateTime|MongoDate $date
     * @return string
     */
    static function toDateString($date){
        if(is_null($date)) return null;
        $date = self::anyToDateTime($date);
        return $date->format(self::$DateFormat);
    }

    /**
     * @static
     * @param int|DateTime|MongoDate $date
     * @return string
     */
    static function toDateTimeString($date){
        if(is_null($date)) return null;
        $date = self::anyToDateTime($date);
        return $date->format(self::$DateTimeFormat);
    }
}
