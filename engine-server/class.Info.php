<?php
/**
 * @desc       хранить текущую версию w frameworka и копирайт
 * @package    w framework
 * @category   engine
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object
 * @version    0.3.0
 */
class Info extends Object {

 const VERSION = '0.3.0';

    public function __construct() {}

    public function getVersion() {
        return self::VERSION;
    }

    public function checkVersion($v) {
        return (version_compare($v,self::VERSION)>=0)?true:false;
    }

    public function getCopyright() {
        return '(c) 2010 - 2011 Checha Andrey';
    }

    public function __toString() {
        return self::VERSION;
    }

}
?>
