<?php

return array(
    'Autoloader'=>array(
        'cache'=>true,
        'cpath'=>AP.SD.PRJ.D.PROJECT.D.'.loaderfilecache',
        'bpath'=>AP.SD.ENGINE.D
    ),
    'Accelerateloader'=>array(
        'cache'=>true,
        'cpath'=>AP.SD.PRJ.D.PROJECT.D.'.loaderfilecache',
        'bpath'=>AP.SD.ENGINE.D,
        'acache'=>false,
        'apath'=>AP.SD.PRJ.D.PROJECT.D.'.aloaderfilecache',
        'className'=>array(// обезательно распологать в правильном порядке, тоесть сначала родительские классы, а потом дочерние классы
            'CCtrlException','DocException','URLException','AuthException','ActionException','RequestException','SessionException',
            'OutputException','InputException','CacheException','ValidateException','ArchiveException','FSException','TemporaryException',
            'DBException',
            'Handling','Capture',
            'Request','CController',
            'Input','Output','OutputBrowser','Session','FileSession','Authorisation','DAuthorisation','Model','Actions','URL',
            'Document','JSON','HTML',
            'DBQuery','DBResult','DBQueryMySQLi','DBResultMySQLi','DBIteratorMySQLi',
            'UTF8'
        )
    ),
    'Handling'=>array(
        'alt'=>'Произошла фатальная ошибка. Попробуйте перезагрузить страницу!',
        'level'=>E_ALL,
        'reporting'=>3,
        'uerror'=>true,
        'char'=>CHAR,
        'mail'=>'xxx',
        'plog'=>AP.SD.PRJ.D.PROJECT.D.'error.log'
    ),
    'DBQueryMySQLi'=>array(
        'host'=>'localhost',
        'user'=>'root',
        'pass'=>'',
        'dbname'=>'dbname',
        'port'=>null,
        'socket'=>null,
        'char'=>'utf8'
    ),
    'DBQueryPostgreSQL'=>array(
    	'host'=>'localhost',
    	'user'=>'postgres',
    	'pass'=>'',
    	'dbname'=>'postgres',
    	'port'=>'5432',
    	'socket'=>null,
        'char'=>'UTF8'
    ),
    'FileSystem'=>array(
        'ow'=>false,
        'AP'=>AP
    ),
    'TemporaryMemcache'=>array(
        'pool'=>array(
            array('host'=>'localhost','port'=>11211)
        ),
        'timeoutlock'=>10000
    ),
    'Actions'=>array(
        'actions'=>array(
            'project1.get.main'=>'sett.php'
        ),
        'folder'=>AP.SD.APP.D,
        'fix'=>true,
        'encodeParam'=>false,
    ),
    'FileSession'=>array(
        'name'=>'wframework',
        'gs'=>2,
        'maxlifetime'=>900,// на сервере
        // параметры для cookie
        'cookielifetime'=>0,// время жизни в браузере
        'cookiepath'=>'',
        'cookiedomain'=>'',
        'cookiesecure'=>false,
        'cookiehttponly'=>true,
        // file
        'folder'=>AP.SD.'temp'.D.'session_'.PROJECT.D
    ),
    'URL'=>array(
        'scheme'=>array(
            'default'=>'http://<domain>{:<port>}/<path>?PRJ=<project>{&<cookieParam>}{&<query>}{#<hash>}',
            'secure'=>'https://<domain>{:<port>}/<path>?PRJ=<project>{&<cookieParam>}{&<query>}{#<hash>}',
            'auto'=>'<protocol>://<domain>{:<port>}/<path>?PRJ=<project>{&<cookieParam>}{&<query>}{#<hash>}',
            'img'=>'<protocol>://<domain>{:<port>}/<path>/{<addpath>/}',
            'upload'=>'<protocol>://<domain>{:<port>}/<path>/{<addpath>}'
        ),
        'SecureName'=>'HTTPS',
        'SecureValue'=>'on',
        'file'=>AP.SD.PRJ.D.PROJECT.D.'prepareurl.php'
    ),
    'MCache'=>array(
        'gs'=>2,
        'lifetime'=>900
    ),
    'UTF8'=>array(
        'char'=>CHAR,
        'phpChar'=>'cp1251'
    ),
    'Mail'=>array(
        'char'=>CHAR,
        'from'=>array('admin'=>'admin@example.com'),// 'from'=>'admin@example.com'
    ),
    'Request'=>array(
        'nameSystemAjax'=>'SYSTEM_SESSION_AJAX',
        'localeName'=>'locale',
        'defCtrl'=>'home',
        'aDefCtrl'=>'index',
        'controllers'=>array(
            'home'=>array(
                'query'=>'html',
                'scheme'=>AP.SD.PRJ.D.PROJECT.D.'index1.xhtml',
                'actions'=>array()
            ),
            'main'=>array(
                'query'=>'json',
                'scheme'=>null,
                'actions'=>array('project1.get.main')
            )
        ),
        'cache'=>false,
        'nameCtrl'=>'C',
        'nameActs'=>'A'
    ),
    'RequestDebug'=>array(
        'filelog'=>AP.SD.PRJ.D.PROJECT.D.'debugrequest.log',
        // тоже что и Request
    ),
    'Model'=>array(
        'bpath'=>AP.SD.APP.D
    ),
    'JSON'=>array(
        'char'=>CHAR,
        'nameArg'=>'X-Requested-With'
    ),
    'HTML'=>array(
        'char'=>CHAR
    ),
    'IMAGE'=>array(
        'type'=>'jpeg'
    ),
    'DAuthorisation'=>array(// DAuthorisation
        'nameSystemData'=>'SYSTEM_SESSION_DATA',
        'role'=>array(
            0=>'guest',
            1=>'user'
        ),
        'rememberme'=>AP.SD.PRJ.D.PROJECT.D.'rememberme.php',// путь к файлу
        'defLocale'=>'ru_UA',
        'dPrivilege'=>AP.SD.PRJ.D.PROJECT.D.'prepareprivilege.php'// путь к файлу в котором определяется динамическая привилегия
    ),
    'CController'=>array(
        'char'=>CHAR,
        'index'=>AP.SD.CENGINE.D.'client.js',
        'config'=>AP.SD.PRJ.D.PROJECT.D.'config.js',
        'fcache'=>AP.SD.PRJ.D.PROJECT.D.'config.cache',
        'settings'=>'controller',
        'addsettings'=>'addsettings'
    ),
    'Input'=>array(
        'validate'=>false
    ),
    'Test'=>array(
        'f'=>true,
        'log'=>AP.SD.PRJ.D.PROJECT.D.'test.log',
        'probability'=>100
    ),
    'Tar'=>array(
        'ow'=>false
    ),
    'TarGz'=>array(
        'ow'=>false
    ),
    'DBBackupMySQLi'=>array(
        'rows'=>2, // количество рядов в inserte при одном запросе
        'probability'=>100, // вероятность запуска зборщика устаревших резервных копий
        'total'=>3, // мин. возможное количество резервных копий
    ),
    'FSBackup'=>array(
        'probability'=>100, // вероятность запуска зборщика устаревших резервных копий
        'total'=>2, // мин. возможное количество резервных копий
    ),
    'SL10n'=>array(
        'folder'=>AP.SD.APP.D
    )
);

?>
