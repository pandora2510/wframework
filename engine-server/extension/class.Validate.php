<?php
/**
 * @desc       Класс для проверки данных на корректность по заданным параметрам
 * @package    w framework
 * @category   engine/extension
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, UTF8, ValidateException
 * @version    0.1.4
 */
class Validate extends Object {

 private $utf8 = null;

    public function __init($a) {
        try {
            if($this->PRO['extension']['UTF8'] instanceof UTF8) $this->utf8 = $this->PRO['extension']['UTF8'];
             else throw new ValidateException('Object: extension/UTF8 - IS NOT AN INSTANCEOF OF CLASS: "UTF8"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(ValidateException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function  __call($name,$arg) { // value,default, ...
        try {
            if(method_exists($this,$name)) {
                if(is_array($arg[0]) or $arg[0] instanceof Prototype) {
                    foreach($arg[0] as $k=>$val) {
                        $arg1 = array($val,isset($arg[1])?$arg[1]:null,isset($arg[2])?$arg[2]:null);
                        $arg[0][$k] = $this->__call($name,$arg1);
                    }
                    return $arg[0];
                } else {
                    return $this->$name(isset($arg[0])?$arg[0]:null,isset($arg[1])?$arg[1]:null,isset($arg[2])?$arg[2]:null);
                }
            } else {
                throw new ValidateException('Validate - "'.$name.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            }
        } catch(ValidateException $e) {
            $this->PRO['error']->handlerror($e);
        }
        return null;
    }

    // 2 - min, 3 - max
    protected function length($val,$def,$arg) {
        if($val === null) return $def;
        $min = isset($arg['min'])?$arg['min']:null;
        $max = isset($arg['max'])?$arg['max']:null;
        if($min > $max and ($min !== null and $max !== null)) return $def;
        $length = $this->utf8->strlen($val);
        if($min and $max === null) {
            return ($length >= $min)?$val:$def;
        } elseif($max and $min === null) {
            return ($length <= $max)?$val:$def;
        } elseif($min and $max) {
            return ($length >= $min and $length <= $max)?$val:$def;
        } else {
            return $def;
        }
    }

    // 2 - regex, 3 - inv
    protected function regex($val,$def,$arg) {
        if($val === null) return $def;
        $regex = isset($arg['regex'])?$arg['regex']:null;
        $inv = (bool)(isset($arg['inv'])?$arg['inv']:null);
        if(!$regex) return $def;
        if($inv) {
            return !preg_match($regex,$val)?$val:$def;
        } else {
            return preg_match($regex,$val)?$val:$def;
        }
    }

    // 2 - arg 3 - inv
    protected function range($val,$def,$arg) {
        if($val === null) return $def;
        $a = isset($arg['range'])?$arg['range']:null;
        $inv = (bool)(isset($arg['inv'])?$arg['inv']:null);
        if(!$a) return $def;
        if(!is_array($a) and !($a instanceof Iterator)) $a = (array)$a;
        if($inv) {
            return !in_array($val,$a)?$val:$def;
        } else {
            return in_array($val,$a)?$val:$def;
        }
    }

    protected function numeric($val,$def,$arg) {
        if($val === null) return $def;
        return is_numeric($val)?$val:$def;
    }

    protected function email($val,$def,$arg) {
        if($val === null) return $def;
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",$val))?$def:$val;
    }

    protected function url($val,$def,$arg) {
        if($val === null) return $def;
        $url = parse_url($val);
        if(isset($url['path']) and (strpos($url['path'],':') !== false) or
            (isset($url['query']) and strpos($url['query'],':') !== false) or
            (isset($url['fragment']) and strpos($url['fragment'],':') !== false) or
            (isset($url['pass']) and strpos($url['pass'],':') !== false) or
            (isset($url['user']) and strpos($url['user'],':') !== false)
        ) return $def;
        return $val;
    }

    // flag
    protected function htmlspecialchar($val,$def,$arg) {
        if($val === null) return $def;
        $flag = isset($arg['flag'])?$arg['flag']:ENT_COMPAT;
        return htmlspecialchars($val,$flag);
    }

    protected function unescape($val,$def,$arg) {
        if($val === null) return $def;
        return stripslashes($val);
    }

    // 2 - dash, 3 - digit
    protected function alpha($val,$def,$arg) {
        if($val === null) return $def;
        $dash = (bool)(isset($arg['dash'])?$arg['dash']:null);
        $dig = (bool)(isset($arg['digit'])?$arg['digit']:null);
        $str = $val;
        $parent = '/^\pL++$/uD';
        if($dig) {
            if($dash) {
                $parent = '/^[-\pL\pN_\s]++$/uD';
            } else {
                $parent = '/^[\pL\pN]++$/uD';
            }
        } else {
            if($dash) {
                $parent = '/^[-\pL_\s]++$/uD';
            } else {
                $parent = '/^[\pL]++$/uD';
            }
        }
        return preg_match($parent,$str)?$val:$def;
    }

}
?>
