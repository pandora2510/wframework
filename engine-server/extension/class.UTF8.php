<?php
/**
 * @desc       Класс для работы с UTF-8
 * @package    w framework
 * @category   engine/extension
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object
 */
class UTF8 extends Object {

 private $mb = false;

    public function __init($a) {
        if(function_exists('mb_convert_encoding')) {
            $this->mb = true;
            mb_internal_encoding($this->SETT['char']);
        }
        if(!function_exists('iconv')) trigger_error('Extension - "iconv" - NOT FOUND',E_USER_ERROR);
    }

    public function convert($str,$from,$to) {// str из в
        if($this->mb) {
            return mb_convert_encoding($str,$to,$from);
        }
        return iconv($from,$to,$str);
    }

    public function is($str) {
        return preg_match('//u',$str);
    }

    public function translit_cl($str) {
        $table = array(
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'YO','Ж'=>'ZH','З'=>'Z',
            'И'=>'I','Й'=>'J','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
            'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'C','Ч'=>'CH','Ш'=>'SH','Щ'=>'CSH',
            'Ь'=>'','Ы'=>'Y','Ъ'=>'','Э'=>'E','Ю'=>'YU','Я' => 'YA',
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z',
            'и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
            'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'csh',
            'ь'=>'','ы'=>'y','ъ'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
            'ї'=>'i','Ї'=>'Yi','є'=>'ie','Є'=>'Ye'
        );

        return str_replace(array_keys($table),array_values($table),$str);
    }

    public function  __call($name,$arg) {
        if(method_exists($this,$name)) {
            return $this->$name($arg);
        } else {
            trigger_error('Method - "'.$name.'" - NOT FOUND',E_USER_ERROR);
        }
    }

    protected function strlen($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        if($this->is($str)) {
            if($this->mb) return mb_strlen($str);
            else return iconv_strlen($str);
        } else {
            return strlen($str);
        }
    }

    protected function trim($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        if($this->is($str)) {
            return $this->convert(trim($this->convert($str,$this->SETT['char'],$this->SETT['phpchar'])),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return strlen($str);
        }
    }
    
    /*
     * @link http://docs.kohanaphp.com/core/utf8#ord
     */
    protected function ord($arg) {
        $chr = isset($arg[0])?$arg[0]:null;
        
        $ord0 = ord($chr);
	if($ord0 >= 0 and $ord0 <= 127) return $ord0;
	if(!isset($chr[1])) {
            trigger_error('Short sequence - at least 2 bytes expected, only 1 seen',E_USER_WARNING);
            return FALSE;
	}

	$ord1 = ord($chr[1]);
	if($ord0 >= 192 and $ord0 <= 223) return ($ord0-192)*64+($ord1-128);
	if(!isset($chr[2])) {
            trigger_error('Short sequence - at least 3 bytes expected, only 2 seen',E_USER_WARNING);
            return FALSE;
	}

	$ord2 = ord($chr[2]);
	if($ord0 >= 224 and $ord0 <= 239) return ($ord0-224)*4096+($ord1-128)*64+($ord2-128);
	if(!isset($chr[3])) {
            trigger_error('Short sequence - at least 4 bytes expected, only 3 seen',E_USER_WARNING);
            return FALSE;
	}

	$ord3 = ord($chr[3]);
	if($ord0 >= 240 and $ord0 <= 247) return ($ord0-240)*262144+($ord1-128)*4096+($ord2-128)*64+($ord3-128);
	if(!isset($chr[4])) {
            trigger_error('Short sequence - at least 5 bytes expected, only 4 seen',E_USER_WARNING);
            return FALSE;
	}

	$ord4 = ord($chr[4]);
	if ($ord0 >= 248 and $ord0 <= 251) return ($ord0-248)*16777216+($ord1-128)*262144+($ord2-128)*4096+($ord3-128)*64+($ord4-128);
	if(!isset($chr[5])) {
            trigger_error('Short sequence - at least 6 bytes expected, only 5 seen',E_USER_WARNING);
            return FALSE;
	}

	if($ord0 >= 252 and $ord0 <= 253) return ($ord0-252)*1073741824+($ord1-128)*16777216+($ord2-128)*262144+($ord3-128)*4096+($ord4-128)*64+(ord($chr[5])-128);

        if($ord0 >= 254 and $ord0 <= 255) {
            trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0,E_USER_WARNING);
            return FALSE;
	}
    }

    protected function strrev($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        if($this->is($str)) {
            return $this->convert(strrev($this->convert($str,$this->SETT['char'],$this->SETT['phpchar'])),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return strrev($str);
        }
    }

    protected function strcspn($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $str1 = isset($arg[1])?$arg[1]:null;
        $start = isset($arg[2])?$arg[2]:null;
        $length = isset($arg[3])?$arg[3]:null;
        if($this->is($str) and $this->is($str1)) {
            return strcspn($this->convert($str,$this->SETT['char'],$this->SETT['phpchar']),$this->convert($str1,$this->SETT['char'],$this->SETT['phpchar']),$start,$length);
        } else {
            return strcspn($str,$str1,$start,$length);
        }
    }

    protected function strspn($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $str1 = isset($arg[1])?$arg[1]:null;
        $start = isset($arg[2])?$arg[2]:null;
        $length = isset($arg[3])?$arg[3]:null;
        if($this->is($str) and $this->is($str1)) {
            return strspn($this->convert($str,$this->SETT['char'],$this->SETT['phpchar']),$this->convert($str1,$this->SETT['char'],$this->SETT['phpchar']),$start,$length);
        } else {
            return strspn($str,$str1,$start,$length);
        }
    }

    protected function stristr($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $str1 = isset($arg[1])?$arg[1]:null;
        if($this->is($str) and $this->is($str1)) {
            return $this->convert(stristr($this->convert($str,$this->SETT['char'],$this->SETT['phpchar']),$this->convert($str1,$this->SETT['char'],$this->SETT['phpchar'])),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return stristr($str,$str1);
        }
    }

    protected function strcasecmp($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $str1 = isset($arg[1])?$arg[1]:null;
        if($this->is($str) and $this->is($str1)) {
            return strcasecmp($this->convert($str,$this->SETT['char'],$this->SETT['phpchar']),$this->convert($str1,$this->SETT['char'],$this->SETT['phpchar']));
        } else {
            return strcasecmp($str,$str1);
        }
    }

    protected function ucfirst($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        if($this->is($str)) {
            return $this->convert(ucfirst($this->convert($str,$this->SETT['char'],$this->SETT['phpchar'])),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return ucfirst($str);
        }
    }

    protected function strtoupper($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        if($this->is($str)) {
            if($this->mb) return mb_strtoupper($str);
            else return $this->convert(strtoupper($this->convert($str,$this->SETT['char'],$this->SETT['phpchar'])),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return strtoupper($str);
        }
    }

    protected function strtolower($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        if($this->is($str)) {
            if($this->mb) return mb_strtolower($str);
            else return $this->convert(strtolower($this->convert($str,$this->SETT['char'],$this->SETT['phpchar'])),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return strtolower($str);
        }
    }

    protected function substr_replace($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $str1 = isset($arg[1])?$arg[1]:null;
        $start = isset($arg[2])?$arg[2]:null;
        $length = isset($arg[3])?$arg[3]:null;
        if($this->is($str) and $this->is($str1)) {
            return $this->convert(substr_replace($this->convert($str,$this->SETT['char'],$this->SETT['phpchar']),$this->convert($str1,$this->SETT['char'],$this->SETT['phpchar']),$start,$length),$this->SETT['phpchar'],$this->SETT['char']);
        } else {
            return substr_replace($str,$str1);
        }
    }

    protected function substr($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $start = isset($arg[1])?$arg[1]:null;
        $length = isset($arg[2])?$arg[2]:null;
        if($this->is($str)) {
            if($this->mb) return mb_substr($str,$start,$length);
            else return iconv_substr($str,$start,$length);
        } else {
            return substr($str,$start,$length);
        }
    }

    protected function strrpos($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $needle = isset($arg[1])?$arg[1]:null;
        $offset = isset($arg[2])?$arg[2]:null;
        if($this->is($str) and $this->is($needle)) {
            if($this->mb) return mb_strrpos($str,$needle,$offset);
            else return iconv_strrpos($str,$needle,$offset);
        } else {
            return strrpos($str,$needle,$offset);
        }
    }

    protected function strpos($arg) {
        $str = isset($arg[0])?$arg[0]:null;
        $needle = isset($arg[1])?$arg[1]:null;
        $offset = isset($arg[2])?$arg[2]:null;
        if($this->is($str) and $this->is($needle)) {
            if($this->mb) return mb_strpos($str,$needle,$offset);
            else return iconv_strpos($str,$needle,$offset);
        } else {
            return strpos($str,$needle,$offset);
        }
    }

}
?>
