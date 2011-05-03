<?php
/**
 * @desc       Класс для проверки полученых файлов на корректность
 * @package    w framework
 * @category   engine/extension
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Validate, Prototype
 * @version    0.1.1
 */
class ValidateExtended extends Validate {

 private $arg = array(1=>'GIF',2=>'JPG',3=>'PNG',4=>'SWF',5=>'PSD',6=>'BMP',
     7=>'TIFF',8=>'TIFF',9=>'JPC',10=>'JP2',11=>'JPX',12=>'JB2',13=>'SWC',
     14=>'IFF',15=>'WBMP',16=>'XBM'
 );

    // __call !!!
    public function  __call($name,$arg) { // value,default, ...
        try {            
            if(method_exists($this,$name)) {
                if(is_array($arg[0]) or $arg[0] instanceof Prototype) {
                    if($name == 'upload' or $name == 'upload_img') {
                        if(isset($arg[0]['error'],$arg[0]['name'],$arg[0]['type'],$arg[0]['size'],$arg[0]['tmp_name']) 
                          and !is_object($arg[0]['error']) and !is_object($arg[0]['tmp_name'])
                          and !is_object($arg[0]['type']) and !is_object($arg[0]['size']) and !is_object($arg[0]['name'])) {
                            return $this->$name(isset($arg[0])?$arg[0]:null,isset($arg[1])?$arg[1]:null,
                                new Prototype((isset($arg[2])?(array)$arg[2]:array('name'=>null,'type'=>null,'size'=>null,'error'=>null,'tmp_name'=>null)))
                            );
                        }
                    }
                    foreach($arg[0] as $k=>$val) {
                        $arg1 = array($val,isset($arg[1])?$arg[1]:null,isset($arg[2])?$arg[2]:null);
                        $arg[0][$k] = $this->__call($name,$arg1);
                    }
                    return $arg[0];
                } else {
                    return $this->$name(isset($arg[0])?$arg[0]:null,isset($arg[1])?$arg[1]:null,
                        new Prototype((isset($arg[2])?(array)$arg[2]:array('name'=>null,'type'=>null,'size'=>null,'error'=>null,'tmp_name'=>null)))
                    );
                }
            } else {
                throw new ValidateException('Validate - "'.$name.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            }
        } catch(ValidateException $e) {
            $this->PRO['error']->handlerror($e);
        }
        return null;
    }

    // size - допустимый размер
    protected function upload($val,$def,$arg) {// val = array(name=>'',type=>'',size=>'',tmp_name=>'',error=>'');
        if($val === null) return $def;
        if(!isset($val['error']) and $val['error'] > 0) return $def;
        if(isset($val['name'],$val['tmp_name']) and is_uploaded_file($val['tmp_name'])) {
            if(isset($val['size'],$arg['size']) and $val['size'] <= $arg['size']) {
                return new Prototype(array(
                    'name'=>$val['name'],'type'=>isset($val['type'])?$val['type']:null,
                    'size'=>$val['size'],'tmp_name'=>$val['tmp_name'],'error'=>$val['error']
                ));
            } else {
                return $def;
            }
        } else {
            return $def;
        }
    }

    // size, exp
    protected function upload_img($val,$def,$arg) {
        if($val === null) return $def;
        if(!isset($val['error']) or $val['error'] > 0) return $def;
        if(isset($val['name'],$val['tmp_name']) and is_uploaded_file($val['tmp_name'])) {
            if(isset($val['size'],$arg['size']) and $val['size'] <= $arg['size']) {
                if(!function_exists('getimagesize')) return $def;
                $arr = getimagesize($val['tmp_name']);
                if(!is_array($arr)) return $def;
                $exp = strtolower($this->arg[$arr[2]]);
                if(isset($arg['exp']) and is_array($arg['exp'])) {
                    if(!in_array($exp,$arg['exp'])) return $def;
                }                
                return new Prototype(array(
                    'name'=>$val['name'],'type'=>isset($val['type'])?$val['type']:null,
                    'size'=>$val['size'],'tmp_name'=>$val['tmp_name'],'error'=>$val['error'],
                    'width'=>$arr[0],'height'=>$arr[1]
                ));
            } else {
                return $def;
            }
        } else {
            return $def;
        }
    }

}
?>
