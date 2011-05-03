<?php
/**
 * @desc       Генерирует каптчу на основе оптической иллюзии Эббингауза
 * @package    w framework
 * @category   engine/extension
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object
 * @version    0.2.2
 */
class ECaptcha extends Object {

 protected $im = null;
 protected $m = 4;
 protected $M = 4;

    public function __init($a) {
        if(!function_exists('gd_info')) trigger_error('Extension: "GD" - NOT FOUND',E_USER_ERROR);
    }

    public function add(array $opt) {
        $opt = new Prototype($opt);
        $s = ($opt['obj'] + $opt['bgObj']*2 + $opt['objD']*2)*$this->m;
        $im = imagecreate((int)$s,(int)$s);

        // копировать часть фона!!!
        imagecopyresampled($im,$this->im,0,0,$opt['x'],$opt['y'],$s*$this->m,$s*$this->m,$s,$s);

        $p = ($opt['bgObj']+$opt['objD']+$opt['obj']/2)*$this->m;
        imagefilledellipse($im,$p,$p,$opt['obj']*$this->m,$opt['obj']*$this->m,
            imagecolorallocate($im,$opt['objColor']['red'],$opt['objColor']['green'],$opt['objColor']['blue'])
        );

        $r = ($opt['obj']/2+$opt['objD']+$opt['bgObj']/2)*$this->m;
        $bp = array('x0'=>$p,'y0'=>$p,'x1'=>$p,'y1'=>$p-$r,'x2'=>$p+$r,'y2'=>$p,'x3'=>$p,'y3'=>$p+$r,'x4'=>$p-$r,'y4'=>$p);

        $offset = mt_rand(1,360);
        $I = 360/$opt['bgObjCount'];
        $N = $opt['bgObjCount'];
        while($N > 0) {
            $offset += $I;
            $bpn = $this->bp($bp,$r,$offset);
            imagefilledellipse($im,$bpn['x'],$bpn['y'],$opt['bgObj']*$this->m,$opt['bgObj']*$this->m,
                imagecolorallocate($im,$opt['bgObjColor']['red'],$opt['bgObjColor']['green'],$opt['bgObjColor']['blue'])
            );
            $N--;
        }

        imagecopyresampled($this->im,$im,$opt['x'],$opt['y'],0,0,$s/$this->M,$s/$this->M,$s,$s);
        imagedestroy($im);

        return array('x'=>$p/$this->M+$opt['x'],'y'=>$p/$this->M+$opt['y'],'r'=>(($opt['obj']*$this->m)/$this->M)/2);
    }

    private function bp(array $c0,$c,$ang) {
        while($ang > 360) {
            $ang = $ang - 360;
        }
        if($ang == 0 || $ang == 360) return array('x'=>$c0['x1'],'y'=>$c0['y1']);
        elseif($ang == 90) return array('x'=>$c0['x2'],'y'=>$c0['y2']);
        elseif($ang == 180) return array('x'=>$c0['x3'],'y'=>$c0['y3']);
        elseif($ang == 270) return array('x'=>$c0['x4'],'y'=>$c0['y4']);

        $bp = array('x'=>0,'y'=>0);
        if($ang < 90) {
            $a = $c*sin(deg2rad($ang));
            $b = $c*cos(deg2rad($ang));
            $bp['x'] = $c0['x0'] + $a;
            $bp['y'] = $c0['y0'] - $b;
        } elseif($ang < 180) {
            $ang -= 90;
            $a = $c*sin(deg2rad($ang));
            $b = $c*cos(deg2rad($ang));
            $bp['x'] = $c0['x0'] + $b;
            $bp['y'] = $c0['y0'] + $a;
        } elseif($ang < 270) {
            $ang -= 180;
            $a = $c*sin(deg2rad($ang));
            $b = $c*cos(deg2rad($ang));
            $bp['x'] = $c0['x0'] - $a;
            $bp['y'] = $c0['y0'] + $b;
        } else {
            $ang -= 270;
            $a = $c*sin(deg2rad($ang));
            $b = $c*cos(deg2rad($ang));
            $bp['x'] = $c0['x0'] - $b;
            $bp['y'] = $c0['y0'] - $a;
        }
        return $bp;
    }

    public function noise(array $opt) {
        $opt = new Prototype($opt);
        if($opt['k'] > 1000) $opt['k'] = 1000;
        $w = imagesx($this->im);
        $h = imagesy($this->im);
        while($opt['k'] > 0) {
            $px = mt_rand(0,$w);
            $py = mt_rand(0,$h);
            $s = mt_rand($opt['min']?$opt['min']:1,($opt['max']?$opt['max']:($w>$h?$h:$w)));
            $color = array('red'=>mt_rand(0,255),'green'=>mt_rand(0,255),'blue'=>mt_rand(0,255));
            imagefilledellipse($this->im,$px,$py,$s,$s,
                imagecolorallocate($this->im,$color['red'],$color['green'],$color['blue'])
            );
            $opt['k'] = $opt['k'] - 1;
        }
    }

    public function create($width,$height,array $bg) {// count - количество ячеек
        $bg = new Prototype($bg);
        $this->im = imagecreate($width,$height);
        imagecolorallocate($this->im,$bg['red'],$bg['green'],$bg['blue']);
    }

    public function image($type) {
        $type = strtolower($type);
        if($type == 'png') return imagepng($this->im);
        elseif($type == 'gif') return imagegif($this->im);
        elseif($type == 'wbmp') return imagewbmp($this->im);
        elseif($type == 'jpeg') return imagejpeg($this->im);
        else return null;
    }

    public static function check(array $data,array $val) {
        if(!isset($data['x']) or $data['x'] <= 0 or !isset($data['y']) or $data['y'] <= 0) return false;
        if(!isset($data['r']) or $data['r'] <= 0) return false;
        if(!isset($val['x']) or $val['x'] <= 0 or !isset($val['y']) or $val['y'] <= 0) return false;
        if($data['x'] == $val['x']) {
            return (abs($data['y'] - $val['y']) <= $data['r'])?true:false;
        } elseif($data['y'] == $val['y']) {
            return (abs($data['x'] - $val['x']) <= $data['r'])?true:false;
        } else {
            $c0 = abs($data['x'] - $val['x']);
            $c1 = abs($data['y'] - $val['y']);
            $c = sqrt(pow($c0,2)+pow($c1,2));
            return ($c <= $data['r'])?true:false;
        }
    }

    public function  __destruct() {
        if(is_resource($this->im)) imagedestroy($this->im);
        $this->im = null;
    }

}
?>
