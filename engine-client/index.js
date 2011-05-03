/**
 * @desc       индексный файл для engine-client
 * @package    w framework
 * @category   index.js
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       ...
 * @version    0.3.7
 */
(function(window) {

    var obj = {};

    // переменные шаблона...
    {addsettings}

    var o = {controller};// переменная, заменяется базовыми настройками
    for(var i in o) obj[i] = o[i];
    // переменнные шаблона...    
    
    // внести свои настройки в переменную obj
    var paths = [], loader = false;
    if(obj['paths'] instanceof Object) {
        for(var j in obj['paths']) {
            var p = obj['paths'][j]+'';
            var pn = location.pathname.replace(/[^\/]*$/i,'');
            if(p.indexOf('http') < 0) p = location.protocol+'//'+location.host+pn+obj['engine-client']+'/'+p;
            paths.push(p);
        }
        delete obj['paths'];
    }

    var id = setInterval(function() {
        if(loader) return;
        // прелоадер
        if(typeof(obj['open_preloader']) == 'function' && document.body) {
            obj['open_preloader']();
            delete obj['open_preloader'];
        }
        
        if(paths.length > 0) {
            var p1 = paths.shift()+'';
            addScript(p1,function() {
                loader = false;
            });
            loader = true;
            return;
        }
        if(!document.body) return;
        clearInterval(id);
        var ff = function() {};
        if(typeof(obj['complete_preload']) == 'function') {
            ff = obj['complete_preload'];
            delete obj['complete_preload'];
        }
        // проверка включены ли cookie
        if(document.cookie.length < 1) warning(obj['notcookie']);
        // вешаем банер об устаревшем браузере
        // w - еще может быть неготова!!!
        if(w.browser.n == 'opera') {
            if(w.browser.v < 11) warning(obj['oldbrowser']);
        } else if(w.browser.n == 'firefox') {
            if(w.browser.v < 4) warning(obj['oldbrowser']);
        } else if(w.browser.n == 'chrome') {
            if(w.browser.v < 8) warning(obj['oldbrowser']);
        } else if(w.browser.n == 'safari') {
            if(w.browser.v < 5) warning(obj['oldbrowser']);
        } else if(w.browser.n == 'msie') {
            if(w.browser.v < 8) warning(obj['oldbrowser']);
        } else {
            // устаревший браузер
            warning(obj['unknownbrowser']);
        }
        
        delete obj['oldbrowser'];
        delete obj['notcookie'];
        delete obj['unknownbrowser'];        

        w(document.body)[obj['namePlg']](obj);
        // framework загружен
        ff();
    },30);

    function warning(param) {
        if(!param || typeof(param) != 'object') param = {};
        if(!param['visibility']) return;
        
        var msg = document.getElementById(param['id']);
        if(!msg || typeof(msg) != 'object') return;
        msg.style.display = 'none';
        var body = document.createElement('div');
        body.setAttribute('class',param['class_body']);
        body.innerHTML = msg.innerHTML;
        var head = document.createElement('div');
        head.setAttribute('class',param['class_head']);
        var close = document.createElement('span');
        close.setAttribute('class',param['class_close']);
        close.innerHTML = '[X]';
        head.appendChild(close);
        w(close).one('click',function(e) {
            setTimeout(function() {
                msg.parentNode.removeChild(msg);
            },10);
        });

        msg.innerHTML = '';
        msg.appendChild(head);
        msg.appendChild(body);
        msg.style.display = 'block';
        document.body.appendChild(msg);
    }

    function addScript(path,callback) {
        var js = document.createElement('script');
        js.setAttribute('type','text/javascript');
        js.setAttribute('src',path);
        js.done = false;
        js.onload = function() {
            if(js.done) return;
            js.done = true;
            callback();
        }
        js.onreadystatechange = function() {
            if(js.done) return;
            if(js.readyState != 'loaded' && js.readyState != 'complete') return;
            js.done = true;
            callback();
        }
        document.getElementsByTagName('head')[0].appendChild(js);
        setTimeout(function(){
            if(!js.done) {
                js.done = true;
                callback();
            }
        },5000);
    }

    // организовать какойто прелоад

})();
