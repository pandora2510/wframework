/**
 * @desc       Динамические областя, один из базовых файлов w frameworka
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       w, ahr
 * @version    0.5.3
 */
(function(w) {

    var $this = null, opt = {},
    loadPage = false, // состояние запроса данных по текущей ссылке
    prevPage = '#',// хранить список страниц, по которым надо осуществлять навигацию
    click = null,// get опция в которой сохраняется id контейнера который получил click
    listInterval = {},
    STORAGE = {}, // объект с пакетом данных из последнего запроса
    defaultSett = {
        //  набор опций по умолчанию!!!
        /*
         * actions:{
            name:{optname:'optval',optname1:'optval2'},
            name1:{}
        }
         */
        actions:{},// name,view,l10n,timeInterval,front-dep-end,param // список экшенов с опциями
        controllers:{},// список контроллеров с опциями // name,actions
        aDefCtrl:'home',// поменять в скрипте
        nameCtrl:'C',
        nameActs:'A',
        nameQuery:'QR',
        sufixName:'actions-',
        timeNav:0.2,// сек.
        pathName:w.window.location.pathname,
        appFolder:'applications',
        noView:null,
        aError:null,
        aTIError:null,
        open:null,
        complete:null,
        domain:null // решение проблем с субдоменами??!!?!
    };

    // системные переменные
    var sts = {}, ssum = 0; // ..........

    // зарезервированные атрибуты
    // data-href, data-ti-url, data-area-cache(!)

    w.fn.extend({
        area:function(options) {
            if($this !== null) return this;
            $this = this;

            opt = w.extend(defaultSett,options || {});

            var lng = getCookie('locale').split('_');
            if(lng[0]) w.area.locale0 = lng[0];
            if(lng[1]) w.area.locale1 = lng[1];
            w.area.nctrl = opt.nameCtrl;
            w.area.nacts = opt.nameActs;
            w.area.nqr = opt.nameQuery;

            setInterval(function(){
                // поиск изменений в #
                if(!loadPage && w.window.location.href != prevPage) {
                    getActions(w.window.location.href);
                }
            },opt.timeNav*1000);

            for(var i in opt.actions) {
                var el = w.window.document.getElementById(opt.sufixName+i);
                if(!el) continue;
                el.style.display = 'none';
                w(el).follow('click',link);
            }

            return $this;
        },
        areaStop:function() {
            var $this = this;
            var id = (typeof($this.getAttribute) == 'function' || (typeof($this.getAttribute) == 'object' && $this.getAttribute))?$this.getAttribute('id'):null;
            if(id) {
                id = (id+'').substr(opt.sufixName.length);
                if(listInterval[id]) {
                    clearInterval(listInterval[id]);
                    delete listInterval[id];
                }
            }
            return $this;
        }
    });

    w.extend({
        hgo:function(URL) {
            var url = w.parse_url(URL);
            if((url.scheme != 'http' || url.scheme != 'https') && url.host != w.window.location.host) return undefined;
            if(!url.fragment || (url.fragment+'').length < 1) w.window.location.hash = url.query; else w.window.location.hash = url.fragment;
            click = null;
            return false;
        }
    });

    w.extend({
        area:{
            ctrl:null,
            nctrl:null,
            acts:null,
            nacts:null,
            nqr:null,
            action:{},
            l10n:{},
            locale0:'ru',
            locale1:'UA'
        }
    });

    function getCookie(name) {
        var matches = w.window.document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
        return matches?decodeURIComponent(matches[1]):'';
    }

    function link(e) {// тег любой, атрибут href или data-href???
        e.stopPropagation();
        //if(e.target.nodeName.toLowerCase() != 'a') return false;
        if(e.target.getAttribute('default') && e.target.nodeName.toLowerCase() == 'a') return undefined;
        var href;
        if(e.target.getAttribute('href')) {
            href = e.target.getAttribute('href');
        } else if(e.target.getAttribute('data-href')) {
            href = e.target.getAttribute('data-href');
        } else {
            return undefined;
        }
        // парсе url, проверка домена и схемы
        var url = w.parse_url(href);
        if((url.scheme != 'http' || url.scheme != 'https') && url.host != w.window.location.host) return undefined;
        // подмена якоря
        // anchor
        if(!url.fragment || (url.fragment+'').length < 1) w.window.location.hash = url.query; else w.window.location.hash = url.fragment;
        click = e.currentTarget.id.substr(opt.sufixName.length);
        return false;
    }

    function getActions(HREF) {
        loadPage = true;
        // start
        if(typeof(opt.open) == 'function') opt.open();

        // получение контроллера
        var ctrl = opt.aDefCtrl, arg = {};        
        w.parse_str(w.window.location.hash.replace(/^#/i,''),arg); // парсим строку на параметры
        if(arg[opt.nameCtrl]) ctrl = arg[opt.nameCtrl];
        // если контроллер отсутствует, контроллер по умолчанию
        if(!opt.controllers[ctrl]) ctrl = opt.aDefCtrl;
        w.area.ctrl = ctrl; // глобальная переменная

        
        // получаем список загружаемых экшенов(+ctrl,+click,-visible,+def)
        var acts = [], i, actions = [];
        // +ctrl
        if(!opt.controllers[ctrl]) opt.controllers[ctrl] = {'actions':[]};
        if(!opt.controllers[ctrl]['actions']) opt.controllers[ctrl]['actions'] = [];
        for(i in opt.controllers[ctrl]['actions']) {
            acts.push(opt.controllers[ctrl]['actions'][i]);
            actions.push(opt.controllers[ctrl]['actions'][i]);
        }
        // +click
        if(click) acts.push(click);        
        // -visible
        var v = getVisible();
        for(i in v) {
            for(var j in acts) {
                if(v[i] == acts[j]) delete acts[j];
            }
        }
        // +def с строки запроса
        if(arg[opt.nameActs]) {
            for(i in arg[opt.nameActs]) {
                acts.push(arg[opt.nameActs][i]);
            }
        }
        // +зависимости
        if(click) {
            if(!opt.actions[click]['front-dep-end']) opt.actions[click]['front-dep-end'] = [];
            for(i in opt.actions[click]['front-dep-end']) {
                acts.push(opt.actions[click]['front-dep-end'][i]);
            }
        }

        // глобальная переменная с экшенами
        w.area.acts = actions;

        // unique acts!!!
        acts = array_unique(acts);
        // пересчитать
        var acts1 = acts; acts = [];
        for(i in acts1) acts.push(acts1[i]);
        
        // параметры запроса
        var query = {};
        query[opt.nameCtrl] = ctrl;
        if(acts instanceof Object && acts.length > 0) {
            query[opt.nameActs] = acts;            
        }

        // добавить параметры, которые должны отправлятся вместе с запросом!!!
        delete arg[w.area.nctrl];
        delete arg[w.area.nacts];

        for(i in arg) { query[i] = arg[i]; }

        //alert(location.protocol+'//'+location.host+'/'+opt.pathName+'?'+w.http_build_query(query));
        
        var pn = w.window.location.pathname.replace(/[^\/]*$/i,'');
        // ajax запрос
        w.ahr({type:'json',
            method:'GET',
            url:w.window.location.protocol+'//'+w.window.location.host+pn+opt.pathName+(((opt.pathName+'').indexOf('?') > -1)?'&':'?')+w.http_build_query(query),
            cache:false,
            open:function() {return true;},
            error:function(xhr,st) {
                if(typeof(opt.aError) == 'function') opt.aError(xhr.responseText,st);
            },
            success:function(data,st) {
                // сравнение visible с actsn
                //STORAGE = data;
                var actAll = getAll();

                for(var i in actAll) {
                    var tmp = false;
                    for(var j in actions) if(actAll[i] == actions[j]) tmp = true;
                    if(tmp) continue;
                    var el = w.window.document.getElementById(opt.sufixName+actAll[i]);
                    if(el) {
                        el.style.display = 'none';
                        
                        //el.innerHTML = '';// чистит незадействованные экшены
                        
                        if(data[actAll[i]]) {
                            var id = actAll[i];
                            setAction(id,
                                {data:data[id],view:opt.actions[id]['view'],l10n:opt.actions[id]['l10n']}
                            );

                            setTimeInterval(id,ctrl);

                            delete data[id];
                        }
                    }
                    // убывать таймеры
                    if(listInterval[actAll[i]]) {
                        clearInterval(listInterval[actAll[i]]);
                        delete listInterval[actAll[i]];
                    }
                }
                
                // ...
                for(i in data) {
                    //
                    setAction(i,{data:data[i],view:opt.actions[i]['view'],l10n:opt.actions[i]['l10n']});
                    // установка interval!!!
                    setTimeInterval(i,ctrl);
                }
            },
            complete:function(xhr,st) {
                var id = setInterval(function(){
                    var s = 0;
                    for(var i in sts) s += sts[i];
                    // избавляемся от бага в opere
                    if(s <= ssum) {
                        clearInterval(id);
                        ssum = 0;
                        sts = {};
                        //finish
                        if(typeof(opt.complete) == 'function') opt.complete();
                        prevPage = HREF;//!!!!!!!!!!!!!!!!!!!!
                        loadPage = false;
                    }
                },150);
            }
        });
    }
    // data-ti-url
    function setTimeInterval(id,c) {
        if(opt.actions[id]['timeInterval'] && opt.actions[id]['timeInterval'] > 0) {
            if(listInterval[id]) return;
            listInterval[id] = setInterval(function(){                
                var q = {};                
                q[opt.nameCtrl] = c;
                q[opt.nameActs] = [id];
                var pn = w.window.location.pathname.replace(/[^\/]*$/i,'');
                var URL = w.window.location.protocol+'//'+w.window.location.host+pn+opt.pathName+(((opt.pathName+'').indexOf('?') > -1)?'&':'?')+w.http_build_query(q);
                var act = w.window.document.getElementById(opt.sufixName+id);
                if(typeof(act) == 'object') {
                    if((act.getAttribute('data-ti-url')+'').length > 0) URL = act.getAttribute('data-ti-url');
                }
                w.ahr({type:'json',
                    method:'GET',
                    url:URL,
                    cache:false,
                    error:function(xhr,st) {
                        if(typeof(opt.aTIError) == 'function') opt.aTIError(xhr.responseText,st);
                    },
                    success:function(data,st) {
                        //STORAGE[id] = data[id];
                        if(!listInterval[id]) return;
                        setAction(id,{data:data[id],view:opt.actions[id]['view'],l10n:opt.actions[id]['l10n']},true);
                    }
                });
            },opt.actions[id]['timeInterval']*1000);
        }
    }

    function setAction(id,arg,ti) {// arg {view,l10n,data,...}
        var act = w.window.document.getElementById(opt.sufixName+id);
        // занесение данных в спец. регистр
        //storage[id] = arg.data;
        // добавлять контейнер в body, если он не существует
        if(!act) {
            var div = w.window.document.createElement('div');
            div.setAttribute('id',opt.sufixName+id);
            // повесить обработчик link
            w(div).follow('click',link);
            $this.appendChild(div);
            act = div;

            // добавить параметры в actions
            
        }

        var fl = function(path) {
            // view
            // вызов нормального view
            //alert();
            if(w.area.action[id] && typeof(w.area.action[id]) == 'function') {
                w.area.action[id](act,arg.data,function(key){
                    return w.area.l10n[id][key]?w.area.l10n[id][key]:key;
                },STORAGE); // шаблон для функции формирующей контент
            } else {
                if(typeof(opt.noView) == 'function') opt.noView(path,id);
            }
            // +show
            act.style.display = '';
            act.style.visibility = 'visible';
            // системные переменные
            if(!ti) {// перенести
                ssum++;
                sts[id] = 1;
            }
        }

        //act.style.display = 'none';
        act.style.visibility = 'hidden';
        //if(!ti) {
        //    clearInterval(listInterval[id]);
        //    delete listInterval[id];
        //}

        act.innerHTML = '';

        var pn = w.window.location.pathname.replace(/[^\/]*$/i,''), p = w.window.location.protocol+'//'+w.window.location.host;
        if(arg.l10n[w.area.locale0]) {
            w.addScript(p+pn+'./'+opt.appFolder+'/'+id+'/'+arg.l10n[w.area.locale0],function() {
                if(arg.view) w.addScript(p+pn+'./'+opt.appFolder+'/'+id+'/'+arg.view,function() {
                    fl(p+pn+'./'+opt.appFolder+'/'+id+'/'+arg.view);
                });
            });
        } else if(arg.view) {
            w.addScript(p+pn+'./'+opt.appFolder+'/'+id+'/'+arg.view,function() {
                fl(p+pn+'./'+opt.appFolder+'/'+id+'/'+arg.view);
            });
        }
    }

    function getVisible() {
        var arg = [];
        for(var i in opt.actions) {
            var el = w.window.document.getElementById(opt.sufixName+i);
            if(!el || el.style.display == 'none') continue;
            //if((el.innerHTML+'').length > 0) continue;
            arg.push(i);
        }
        return arg;
    }
    
    function getAll() {
        var arg = [];
        for(var i in opt.actions) {
            var el = w.window.document.getElementById(opt.sufixName+i);
            arg.push(i);
        }
        return arg;
    }

    /*
     * @link http://phpjs.org/functions/array_unique
     */
    function array_unique(inputArr) {
        var key = '', tmp_arr2 = {}, val = '';
        var __array_search = function(needle,haystack) {
            var fkey = '';
            for(fkey in haystack) {
                if(haystack.hasOwnProperty(fkey)) {
                    if((haystack[fkey] + '') === (needle + '')) return fkey;
                }
            }
            return false;
        };

        for(key in inputArr) {
            if(inputArr.hasOwnProperty(key)) {
                val = inputArr[key];
                if(false === __array_search(val,tmp_arr2)) tmp_arr2[key] = val;
            }
        }

        return tmp_arr2;
    }

})(W);
