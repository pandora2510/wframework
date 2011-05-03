<?php 
/**
 * @desc       Класс для обработки возникнувших ошибок
 * @package    w framework
 * @category   engine/error
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Capture, Object, Mail
 * @version    0.2.1
 */
class Handling extends Object {
	
 private $h = null;
	
    public function __init($a) {
	if($this->SETT['level'] > 0) error_reporting($this->SETT['level']);
	if($this->SETT['uerror']) {
            $this->h = new Capture();
	}
    }

    // 0 - alt, 1 - dis, 2 - mail, 3 - log
    final public function handlerror(ErrorException $e) {
	switch($this->SETT['reporting']) {
            case 0: $this->alt(); break;
            case 1: $this->display($e); break;
            case 2: $this->mail($e); $this->alt(); break;
            case 3: $this->log($e); $this->alt(); break;
            default: $this->alt();
	}
    }
	
    protected function display($e) {
    	if(!headers_sent()) header('Content-type: text/html; charset='.$this->SETT['char']);
	?><table>
            <tbody>
		<tr><td>Date:</td><td><?php echo date('Y/m/d H:i:s'); ?></td></tr>
                <tr><td>Class:</td><td><?php echo get_class($e); ?></td></tr>
		<tr><td>Message:</td><td><?php echo $e->getMessage(); ?></td></tr>
		<tr><td>Code:</td><td><?php echo $e->getCode(); ?></td></tr>
		<tr><td>File:</td><td><?php echo $e->getFile(); ?></td></tr>
		<tr><td>Line:</td><td><?php echo $e->getLine(); ?></td></tr>
		<tr><td>Stack:</td><td><?php echo $e->getTraceAsString(); ?></td></tr>
            </tbody>
	</table><?php
    }
	
    protected function log($e) {
        if(!file_exists(dirname($this->SETT['plog']))) {
            mkdir(dirname($this->SETT['plog']),'0777',true);
	}
	$d = '<error>
            <date>'.date('Y/m/d H:i:s').'</date>
            <class>'.get_class($e).'</class>
            <message>'.$e->getMessage().'</message>
            <code>'.$e->getCode().'</code>
            <file>'.$e->getFile().'</file>
            <line>'.$e->getLine().'</line>
            <stack>'.$e->getTraceAsString().'</stack>
	</error>';
	error_log($d."\n",3,$this->SETT['plog']);
    }
	
    protected function mail($e) {// проверить как работает
	$m = new Mail($this->PRO);
        $m->to($this->SETT['mail'],'wframework');
        $m->subject($e->getMessage());
        $msg = '<table>
            <tbody>
		<tr><td>Date:</td><td>'.date('Y/m/d H:i:s').'</td></tr>
                <tr><td>Class:</td><td>'.get_class($e).'</td></tr>
		<tr><td>Message:</td><td>'.$e->getMessage().'</td></tr>
		<tr><td>Code:</td><td>'.$e->getCode().'</td></tr>
		<tr><td>File:</td><td>'.$e->getFile().'</td></tr>
		<tr><td>Line:</td><td>'.$e->getLine().'</td></tr>
		<tr><td>Stack:</td><td>'.$e->getTraceAsString().'</td></tr>
            </tbody>
	</table>';
        $m->message($msg);
        $m->send();
    }
	
    protected function alt() {
	if(!headers_sent()) header('Content-type: text/html; charset='.$this->SETT['char']);
	echo $this->SETT['alt'];
    }

    public function __destruct() {
	$this->h->__destruct();
    }
	
}
?>
