<?php
/**
 * @desc       Класс для измерения затраченных ресурсов
 * @package    w framework
 * @category   engine/debug
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ErrorException
 */
class Test extends Object {

 const D = ' | ';

    public function  __init($a) {}

    public function gettime($stime) {
        $stime = explode(' ',$stime);
        $stime = $stime[0]+$stime[1];
        $ftime = explode(' ',microtime());
        $ftime = $ftime[0]+$ftime[1];
        $ftime = $ftime-$stime;
        return $this->SETT['f']?number_format($ftime,6,'.',''):$ftime;
    }

    public function getmemory($smem,$peak=false) {
        $smem = ($peak?memory_get_peak_usage():memory_get_usage())-$smem;
        return $this->SETT['f']?number_format($smem,0,'.',' '):$smem;
    }

    public function getcpu() {
        $time = array('user'=>'unknown','system'=>'unknown');
        if(function_exists('getrusage')) {
            $arg = getrusage();
            $time['user'] = ($arg['ru_utime.tv_sec'] + $arg['ru_utime.tv_usec']/1000000);
            $time['user'] = $this->SETT['f']?number_format($time['user'],6,'.',''):$time['user'];
            $time['system'] = ($arg['ru_stime.tv_sec'] + $arg['ru_stime.tv_usec']/1000000);
            $time['system'] = $this->SETT['f']?number_format($time['system'],6,'.',''):$time['system'];
        }
        return $time;
    }

    public function geturl() {
        // проверить как работает c https
        $secure = false;
        try {
            $secure = getenv('HTTPS');
        } catch(ErrorException $e) {
            // ничего неделать
        }
        return (empty($secure)?'http':'https').'://'.getenv('HTTP_HOST').getenv('REQUEST_URI');
    }

    // способы вывода информации!!!
    public function tofile($file=false,$str=null) {
        if($file === false or $str === null) if(mt_rand(1,100) > $this->SETT['probability']) return null;
        $file = $file===false?$this->SETT['log']:$file;
        if(!is_file($file)) {
            if(!file_exists(dirname($file))) mkdir(dirname($file),'0777',true);
	}
        $str = $str===null?$this->generate():$str;
        $f = fopen($file,'a');
        flock($f,LOCK_EX);
        $st = fwrite($f,$str);
        flock($f,LOCK_UN);
        fclose($f);
	return (bool)$st;
    }

    protected function generate() {
        $arg = $this->getcpu();
        $str = date('Y/m/d H:i:s').self::D.$this->gettime(STARTTIME).self::D.$this->getmemory(STARTMEMORY).self::D.$this->getmemory(STARTPEAKMEMORY,true);
        $str .= self::D.$arg['user'].self::D.$arg['system'].self::D.$this->geturl()."\n";
        return $str;
    }

}
?>
