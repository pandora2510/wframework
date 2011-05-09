/**
 * @desc       w
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       ...
 */
(function(window) {
    
    var o = function(obj) {// первый символ #; #id
        if(typeof(obj) != 'object' && typeof(obj) != 'function') 
            obj = o.window.document.getElementById((obj+'').substr(1));
        if(!obj) obj = {};
        for(var i in o.fn) {
            obj[i] = o.fn[i];
        }
        //return o.extend(o.fn,obj);// ???
        return obj;
    }

    window.w = window.W = o;

    function cloneObj(o) {
	if(typeof(o) !== "object" || !o)  return o;
	var c = (o.pop && typeof(o.pop)==="function")?[]:{};
	var p, v;
	for(p in o) {
            if(o.hasOwnProperty(p)) {
                v = o[p];
		c[p] = cloneObj(v);
            }
	}
	return c;
    }

    var extend = function(arr,arr1) {
        var i;
        if(arr1) {
            var cl = cloneObj(arr);
            for(i in arr1) {
                cl[i] = arr1[i];
            }
            return cl;
        } else {
            for(i in arr) {
                if(i == 'fn') continue;
                this[i] = arr[i];
            }
            return undefined;
        }
    }

    o.extend = extend;
    o.fn = {extend:extend};

    o.window = function(win) {
        o.window.document = win.document;
        o.window.history = win.history;
        o.window.location = win.location;
        o.window.navigator = win.navigator;
        o.window.win = win;

        win.w = win.W = o;
    }
    
    o.window.win = window;
    o.window.document = window.document;
    o.window.history = window.history;
    o.window.location = window.location;
    o.window.navigator = window.navigator;    

    function getUA() {
        var ua = o.window.navigator.userAgent, b = {n:'unknown',v:'unknown',version:'unknown'};
        if(/chrome/i.test(ua)) {
            b.n = 'chrome';
            b.version = ua.match(/chrome\/([0-9\.]+)/i)[1];
            b.v = (b.version+'').split('.',1);
            b.v = b.v[0];
        } else if(/opera/i.test(ua)) {
            b.n = 'opera';
            b.version = ua.match(/version\/([0-9\.]+)/i)[1] || ua.match(/opera\/([0-9\.]+)/i)[1];
            b.v = (b.version+'').split('.',1);
            b.v = b.v[0];
        } else if(/firefox/i.test(ua)) {
            b.n = 'firefox';
            b.version = ua.match(/firefox\/([0-9\.]+)/i)[1];
            b.v = (b.version+'').split('.',1);
            b.v = b.v[0];
        }else if(/msie/i.test(ua)) {
            b.n = 'msie';
            b.version = ua.match(/msie ([0-9\.]+)/i)[1];
            b.v = (b.version+'').split('.',1);
            b.v = b.v[0];
	} else if(/safari/i.test(ua)) {
            b.n = 'safari';
            b.version = ua.match(/version\/([0-9\.]+)/i)[1] || ua.match(/safari\/([0-9\.]+)/i)[1];
            b.v = (b.version+'').split('.',1);
            b.v = b.v[0];
	}
        return b;
    }
    
    o.extend({
        browser:getUA()
    });

    o.extend({
        getAll:function(el,tname) {// + поиск в iframe
            if(typeof(el) != 'object') el = o.window.document.body;
            var arg = [];
            if(!tname) tname = '*';
            var n = (tname+'').split(',');
            for(var i in n) {
                var t = el.getElementsByTagName(n[i]);
                for(var y=0; y<t.length; y++) {
                    if(typeof(t[y]) != 'object') continue;
                    arg.push(t[y]);
                }
            }
            // поиск в iframe, поддержывается только одн уровень
            var ifs = el.getElementsByTagName('iframe');
            for(var j=0; j<ifs.length; j++) {
                var doc = ifs[j].contentDocument || ifs[j].contentWindow.document || ifs[j].document;
                var els = [];
                _getAll(doc.body,tname,els);
                for(var k=0; k<els.length; k++) {
                    arg.push(els[k]);
                }
            }
            return arg;
        },
        createIFrame:function(el,src,visible) {
            var c = o.window.document.createElement('div');
            c.setAttribute('id','cIF_'+(new Date()).getTime());
            if(!visible) c.style.display = 'none';
            var ifr = (o.browser.n == 'msie')?'<iframe name="'+c.id+'" src="'+src+'">':'iframe';
            if(o.browser.n == 'msie' && o.browser.v == 9) ifr = 'iframe';
            ifr = o.window.document.createElement(ifr);
            ifr.setAttribute('src',src);
            with(ifr) {
                name = c.id;
                setAttribute("name",c.id);
            }
            c.appendChild(ifr);
            try {el.appendChild(c);} catch(E) {o.window.document.body.appendChild(c);}
            return ifr;
        }
    });

    function inarray(val,obj) {
        if(obj && typeof(obj.pop) == 'function') {
            for(var i=0; i<obj.length; i++) {
                if(val == obj[i]) return true;
            }
        } else if(obj && typeof(obj) == 'object') {
            for(var j in obj) {
                if(val == obj[j]) return true;
            }
        }
        return false;
    }

    function _getAll(el,tname,arr) {
        if(!arr || typeof(arr.pop) != 'function') arr = [];
        if(tname == '*') return arr;
        tname = (tname+'').toLowerCase();
        var n = tname.split(',');
        for(var i=0; i<el.children.length; i++) {
            if(inarray(el.children[i].nodeName.toLowerCase(),n)) arr.push(el.children[i]);
            _getAll(el.children[i],tname,arr);
        }
    }

    // события ................................................................/
    /*
     * @link http://javascript.ru/, http://jquery.com/
     */
    var guid = 0;

    function eventFix(event) {
        event = event || o.window.win.event;
        if(event.isFixed) return event;
        event.isFixed = true;

        event.preventDefault = event.preventDefault || function(){this.returnValue = false;}
        event.stopPropagation = event.stopPropagaton || function(){this.cancelBubble = true;}
        
        if(!event.target) event.target = event.srcElement;
        if(event.target.nodeType === 3 ) event.target = event.target.parentNode;
        
        if(!event.relatedTarget && event.fromElement) event.relatedTarget = event.fromElement==event.target?event.toElement:event.fromElement;
        if(event.pageX == null && event.clientX != null ) {
            var doc = o.window.document.documentElement, body = o.window.document.body;
            event.pageX = event.clientX+(doc && doc.scrollLeft || body && body.scrollLeft || 0)-(doc && doc.clientLeft || body && body.clientLeft || 0);
            event.pageY = event.clientY+(doc && doc.scrollTop  || body && body.scrollTop  || 0)-(doc && doc.clientTop  || body && body.clientTop  || 0);
	}

        if(event.which == null && (event.charCode != null || event.keyCode != null)) event.which = event.charCode!=null?event.charCode:event.keyCode;
        if(!event.metaKey && event.ctrlKey ) event.metaKey = event.ctrlKey;
        if(!event.which && event.button !== undefined ) event.which = (event.button & 1?1:(event.button & 2?3:(event.button & 4?2:0)));

        return event;
    }

    function eventHandle(event) {
        event = eventFix(event);
        if(!event.currentTarget) event.currentTarget = this;
        var handlers = this.events[event.type];
        for(var i in handlers ) {
            var handler = handlers[i];
            var ret = handler.call(this,event);
            if(ret === false) {
                event.preventDefault();
                event.stopPropagation();
            }
        }
    }

    function eventAdd(e,type,callback) {
        if(typeof(callback) != 'function') callback = function(e) {};
        if(e.setInterval && (e != o.window.win && !e.frameElement)) e = o.window.win;

        if(!callback.guid) callback.guid = ++guid;

        if(!e.events) {
            e.events = {}
            e.handle = function(event) {
                return eventHandle.call(e,event);
            }
        }

        if(!e.events[type]) {
            e.events[type] = {};
            if(e.addEventListener) e.addEventListener(type,e.handle,false);
             else if (e.attachEvent) e.attachEvent("on"+type,e.handle);
        }
        e.events[type][callback.guid] = callback;
    }

    function eventRemove(e,type,callback) {
        var i;
        var handlers = e.events && e.events[type];
        if(!handlers) return;
        
        if(!callback) {
            for(i in handlers ) delete e.events[type][i];// delete e.events[type][i];
            return;
        }

        delete handlers[callback.guid];

        for(i in handlers) return;
	if(e.removeEventListener) e.removeEventListener(type,e.handle,false);
	 else if(e.detachEvent) e.detachEvent("on"+type,e.handle);
        delete e.events[type];

	for(i in e.events) return;
        try {
            delete e.handle;
	    delete e.events;
        } catch(E) {// ie
	    e.removeAttribute("handle");
	    e.removeAttribute("events");
	}
    }

    o.fn.extend({
        follow:function(type,callback) {
            if(this.nodeType === 3 || this.nodeType === 8) return this;
            eventAdd(this,type,callback);
            return this;
        },
        ufollow:function(type,callback) {
            if(this.nodeType === 3 || this.nodeType === 8) return this;
            eventRemove(this,type,callback);
            return this;
        },
        one:function(type,callback) {
            if(this.nodeType === 3 || this.nodeType === 8) return this;
            var el = this, f = function(e) {
                callback(e);
                eventRemove(el,type,f);
            };
            eventAdd(el,type,f);
            return el;
        }
    });
    // ......................................................................../

    // val...................................................................../
    o.fn.extend({
        val:function(val) {// this - element
            var i;
            if(typeof(val) == 'undefined') {
                if(!this.type) return this.innerHTML;
                if(this.type == 'select-multiple' || this.type == 'select-one') {
                    var arg = [];
                    for(i=0; i<this.options.length; i++) {
                        if(this.options[i].selected) arg.push(this.options[i].value);
                    }
                    return arg.length<1?this.value:(arg.length==1?arg[0]:arg);
                } else if(this.type == 'checkbox' || this.type == 'radio') {
                    if(this.checked) return this.value;
                } else {
                    return this.value;
                }
                return '';
            } else {
                var f = function(arr,val) {
                    for(var i in arr) {
                        if(arr[i] == val) return true;
                    }
                    return false;
                }
                if(!this.type) this.innerHTML = val;
                if(this.type == 'select-multiple' || this.type == 'select-one') {
                    if(typeof(val) != 'object') val = [val];
                    var def = false;
                    for(var j=0; j<this.options.length; j++) {
                        if(f(val,this.options[j].value)) {
                            def = true;
                            this.options[j].selected = true;
                        }
                    }
                    if(!def) this.options[0].selected = true;
                } else if(this.type == 'checkbox' || this.type == 'radio') {
                    if(this.value == val) this.checked = true;
                } else {
                    this.value = (val && typeof(val)=='object')?o.http_build_query(val):val;
                }
            }
        }
    });
    // ......................................................................../

    // addscript.............................................................../
    var domain = [];
    
    o.extend({
        addScript:function(path,callback) {
            var f = function(path) {
                var js = o.window.document.getElementsByTagName('script');
                for(var i in js) {
                    if((js[i].src+'').indexOf(path) >= 0) return true;
                }
                return false;
            }, checkD = function(path) {
                var d = o.parse_url(path,'PHP_URL_HOST');
                if(!d || d == o.window.location.host) return true;
                for(var i in domain) {
                    if(d == domain[i]) return true;
                }
                return false;
            }
            if(!f(path)) {
                if(!checkD(path)) throw new Error('violation of security policy browser');
                var js = o.window.document.createElement('script');
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
                o.window.document.getElementsByTagName('head')[0].appendChild(js);
                setTimeout(function(){// защита от пустых файлов в опере
                    if(!js.done) callback();
                },10000);
            } else {
                callback();
            }
        },
        addDomain:function(d) {
            domain.push(d);
        }
    });

    // вспомагательные функции................................................./
    o.extend({
        /*
         * @link http://phpjs.org/functions/parse_url
         */
        parse_url:function(str,component) {
            var o = {
                strictMode:false,
                key:["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
                q:{
                    name:"queryKey",
                    parser:/(?:^|&)([^&=]*)=?([^&]*)/g
                },
                parser: {
                    strict:/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                    loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
                }
            };
            var m = o.parser[o.strictMode?"strict":"loose"].exec(str),
                uri = {},
                i = 14;
            while(i--) {uri[o.key[i]] = m[i] || "";}
            switch (component) {
                case 'PHP_URL_SCHEME':return uri.protocol;
                case 'PHP_URL_HOST':return uri.host;
                case 'PHP_URL_PORT':return uri.port;
                case 'PHP_URL_USER':return uri.user;
                case 'PHP_URL_PASS':return uri.password;
                case 'PHP_URL_PATH':return uri.path;
                case 'PHP_URL_QUERY':return uri.query;
                case 'PHP_URL_FRAGMENT':return uri.anchor;
                default:
                    var retArr = {};
                    if(uri.protocol !== '') retArr.scheme=uri.protocol;
                    if(uri.host !== '') retArr.host=uri.host;
                    if(uri.port !== '') retArr.port=uri.port;
                    if(uri.user !== '') retArr.user=uri.user;
                    if(uri.password !== '') retArr.pass=uri.password;
                    if(uri.path !== '') retArr.path=uri.path;
                    if(uri.query !== '') retArr.query=uri.query;
                    if(uri.anchor !== '') retArr.fragment=uri.anchor;
                    return retArr;
            }
        },
        /*
         * @link http://phpjs.org/functions/parse_str
         */
        parse_str:function(str,array) {
            var glue1 = '=', glue2 = '&', array2 = String(str).replace(/^&?([\s\S]*?)&?$/, '$1').split(glue2), i, j, chr, tmp, key, value, bracket, keys, evalStr, that = this,
            fixStr = function (str) {
                // decodeURIComponent((str+'').replace(/\+/g, '%20'))
                // return that.urldecode(str).replace(/([\\"'])/g, '\\$1').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
                return decodeURIComponent((str+'').replace(/\+/g, '%20')).replace(/([\\"'])/g, '\\$1').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
            };

            if(!array) array = this.window;

            for(i=0; i<array2.length; i++) {
                tmp = array2[i].split(glue1);
                if(tmp.length < 2) tmp = [tmp, ''];
                key = fixStr(tmp[0]);
                value = fixStr(tmp[1]);
                while(key.charAt(0) === ' ') key = key.substr(1);
                if(key.indexOf('\0') !== -1) key = key.substr(0,key.indexOf('\0'));
                if(key && key.charAt(0) !== '[') {
                    keys = [];
                    bracket = 0;
                    for(j=0; j<key.length; j++) {
                        if(key.charAt(j) === '[' && !bracket) bracket = j + 1;
                        else if(key.charAt(j) === ']') {
                            if(bracket) {
                                if(!keys.length) keys.push(key.substr(0,bracket-1));
                                keys.push(key.substr(bracket,j - bracket));
                                bracket = 0;
                                if(key.charAt(j + 1) !== '[') break;
                            }
                        }
                    }
                    if(!keys.length) keys = [key];
                    for(j=0; j<keys[0].length; j++) {
                        chr = keys[0].charAt(j);
                        if(chr === ' ' || chr === '.' || chr === '[') keys[0] = keys[0].substr(0,j) + '_' + keys[0].substr(j+1);
                        if(chr === '[') break;
                    }
                    evalStr = 'array';
                    for(j=0; j<keys.length; j++) {
                        key = keys[j];
                        if((key !== '' && key !== ' ') || j === 0) key = "'"+key+"'";
                        else key = eval(evalStr+'.push([]);')-1;
                        evalStr += '['+key+']';
                        if(j !== keys.length - 1 && eval('typeof ' + evalStr) === 'undefined') eval(evalStr + ' = [];');
                    }
                    evalStr += " = '"+value+"';\n";
                    eval(evalStr);
                }
            }
        },
        /*
         * @link http://phpjs.org/functions/json_decode
         */
        json_decode:function(str_json) {
            var json = this.window.JSON;
            if(typeof json === 'object' && typeof json.parse === 'function') {
                try {
                    return json.parse(str_json);
                } catch(err) {
                    if (!(err instanceof SyntaxError)) {
                        throw new Error('Unexpected error type in json_decode()');
                    }
                    this.php_js = this.php_js || {};
                    this.php_js.last_error_json = 4; // usable by json_last_error()
                    return null;
                }
            }

            var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
            var j;
            var text = str_json;

            cx.lastIndex = 0;
            if(cx.test(text)) {
                text = text.replace(cx,function (a) {
                    return '\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);
                });
            }

            if((/^[\],:{}\s]*$/).
                test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
                    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                    replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

                j = null;
                try {j = eval('(' + text + ')');} catch(E) {}
                return j;
            }

            this.php_js = this.php_js || {};
            this.php_js.last_error_json = 4; // usable by json_last_error()
            return null;
        },
        /*
         * @link http://phpjs.org/functions/http_build_query
         */
        http_build_query:function(formdata, numeric_prefix, arg_separator) {
            var urlencode = function(str) {
                str = (str+'').toString();
                return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27')
                    .replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
            }
            var value, key, tmp = [];
            var _http_build_query_helper = function(key,val,arg_separator) {
                var k, tmp = [];
                if(val === true) {
                    val = "1";
                } else if(val === false) {
                    val = "0";
                }
                if(val !== null && typeof(val) === "object") {
                    for(k in val) {
                        if(val[k] !== null) {
                            tmp.push(_http_build_query_helper(key + "[" + k + "]",val[k],arg_separator));
                        }
                    }
                    return tmp.join(arg_separator);
                } else if(typeof(val) !== "function") {
                    return urlencode(key)+"="+urlencode(val);
                } else {
                    throw new Error('There was an error processing for http_build_query().');
                }
            };

            if(!arg_separator) {
                arg_separator = "&";
            }
            for(key in formdata) {
                value = formdata[key];
                if(numeric_prefix && !isNaN(key)) {
                    key = String(numeric_prefix) + key;
                }
                tmp.push(_http_build_query_helper(key,value,arg_separator));
            }

            return tmp.join(arg_separator);
        },
        /*
         * @link http://phpjs.org/functions/md5
         */
        md5:function(str) {
            var xl;
            var rotateLeft = function(lValue,iShiftBits) {return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));};

            var addUnsigned = function(lX,lY) {
                var lX4, lY4, lX8, lY8, lResult;
                lX8 = (lX & 0x80000000);
                lY8 = (lY & 0x80000000);
                lX4 = (lX & 0x40000000);
                lY4 = (lY & 0x40000000);
                lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
                if(lX4 & lY4) return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
                if(lX4 | lY4) {
                    if(lResult & 0x40000000) return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                    else return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                } else {
                    return (lResult ^ lX8 ^ lY8);
                }
            };

            var _F = function(x,y,z) {return (x & y) | ((~x) & z);};
            var _G = function(x,y,z) {return (x & z) | (y & (~z));};
            var _H = function(x,y,z) {return (x ^ y ^ z);};
            var _I = function(x,y,z) {return (y ^ (x | (~z)));};
            var _FF = function(a,b,c,d,x,s,ac) {
                a = addUnsigned(a,addUnsigned(addUnsigned(_F(b,c,d),x),ac));
                return addUnsigned(rotateLeft(a,s),b);
            };
            var _GG = function(a,b,c,d,x,s,ac) {
                a = addUnsigned(a,addUnsigned(addUnsigned(_G(b,c,d),x),ac));
                return addUnsigned(rotateLeft(a,s),b);
            };
            var _HH = function(a,b,c,d,x,s,ac) {
                a = addUnsigned(a,addUnsigned(addUnsigned(_H(b,c,d),x),ac));
                return addUnsigned(rotateLeft(a,s),b);
            };
            var _II = function(a,b,c,d,x,s,ac) {
                a = addUnsigned(a,addUnsigned(addUnsigned(_I(b,c,d),x),ac));
                return addUnsigned(rotateLeft(a,s),b);
            };

            var convertToWordArray = function (str) {
                var lWordCount;
                var lMessageLength = str.length;
                var lNumberOfWords_temp1 = lMessageLength + 8;
                var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
                var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
                var lWordArray = new Array(lNumberOfWords - 1);
                var lBytePosition = 0;
                var lByteCount = 0;
                while(lByteCount<lMessageLength) {
                    lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                    lBytePosition = (lByteCount % 4) * 8;
                    lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
                    lByteCount++;
                }
                lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                lBytePosition = (lByteCount % 4) * 8;
                lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
                lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
                lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
                return lWordArray;
            };

            var wordToHex = function(lValue) {
                var wordToHexValue = "",
                    wordToHexValue_temp = "",
                    lByte, lCount;
                for(lCount=0; lCount<=3; lCount++) {
                    lByte = (lValue >>> (lCount * 8)) & 255;
                    wordToHexValue_temp = "0" + lByte.toString(16);
                    wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
                }
                return wordToHexValue;
            };

            var x = [], k, AA, BB, CC, DD, a, b, c, d, S11 = 7, S12 = 12, S13 = 17,
                S14 = 22, S21 = 5, S22 = 9, S23 = 14, S24 = 20, S31 = 4, S32 = 11,
                S33 = 16, S34 = 23, S41 = 6, S42 = 10, S43 = 15, S44 = 21;

            //str = this.utf8_encode(str);
            x = convertToWordArray(str);
            a = 0x67452301;
            b = 0xEFCDAB89;
            c = 0x98BADCFE;
            d = 0x10325476;

            xl = x.length;
            for(k=0; k<xl; k+=16) {
                AA = a;
                BB = b;
                CC = c;
                DD = d;
                a = _FF(a,b,c,d,x[k+0],S11,0xD76AA478);
                d = _FF(d,a,b,c,x[k+1],S12,0xE8C7B756);
                c = _FF(c,d,a,b,x[k+2],S13,0x242070DB);
                b = _FF(b,c,d,a,x[k+3],S14,0xC1BDCEEE);
                a = _FF(a,b,c,d,x[k+4],S11,0xF57C0FAF);
                d = _FF(d,a,b,c,x[k+5],S12,0x4787C62A);
                c = _FF(c,d,a,b,x[k+6],S13,0xA8304613);
                b = _FF(b,c,d,a,x[k+7],S14,0xFD469501);
                a = _FF(a,b,c,d,x[k+8],S11,0x698098D8);
                d = _FF(d,a,b,c,x[k+9],S12,0x8B44F7AF);
                c = _FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
                b = _FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
                a = _FF(a,b,c,d,x[k+12],S11,0x6B901122);
                d = _FF(d,a,b,c,x[k+13],S12,0xFD987193);
                c = _FF(c,d,a,b,x[k+14],S13,0xA679438E);
                b = _FF(b,c,d,a,x[k+15],S14,0x49B40821);
                a = _GG(a,b,c,d,x[k+1],S21,0xF61E2562);
                d = _GG(d,a,b,c,x[k+6],S22,0xC040B340);
                c = _GG(c,d,a,b,x[k+11],S23,0x265E5A51);
                b = _GG(b,c,d,a,x[k+0],S24,0xE9B6C7AA);
                a = _GG(a,b,c,d,x[k+5],S21,0xD62F105D);
                d = _GG(d,a,b,c,x[k+10],S22,0x2441453);
                c = _GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
                b = _GG(b,c,d,a,x[k+4],S24,0xE7D3FBC8);
                a = _GG(a,b,c,d,x[k+9],S21,0x21E1CDE6);
                d = _GG(d,a,b,c,x[k+14],S22,0xC33707D6);
                c = _GG(c,d,a,b,x[k+3],S23,0xF4D50D87);
                b = _GG(b,c,d,a,x[k+8],S24,0x455A14ED);
                a = _GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
                d = _GG(d,a,b,c,x[k+2],S22,0xFCEFA3F8);
                c = _GG(c,d,a,b,x[k+7],S23,0x676F02D9);
                b = _GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
                a = _HH(a,b,c,d,x[k+5],S31,0xFFFA3942);
                d = _HH(d,a,b,c,x[k+8],S32,0x8771F681);
                c = _HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
                b = _HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
                a = _HH(a,b,c,d,x[k+1],S31,0xA4BEEA44);
                d = _HH(d,a,b,c,x[k+4],S32,0x4BDECFA9);
                c = _HH(c,d,a,b,x[k+7],S33,0xF6BB4B60);
                b = _HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
                a = _HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
                d = _HH(d,a,b,c,x[k+0],S32,0xEAA127FA);
                c = _HH(c,d,a,b,x[k+3],S33,0xD4EF3085);
                b = _HH(b,c,d,a,x[k+6],S34,0x4881D05);
                a = _HH(a,b,c,d,x[k+9],S31,0xD9D4D039);
                d = _HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
                c = _HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
                b = _HH(b,c,d,a,x[k+2],S34,0xC4AC5665);
                a = _II(a,b,c,d,x[k+0],S41,0xF4292244);
                d = _II(d,a,b,c,x[k+7],S42,0x432AFF97);
                c = _II(c,d,a,b,x[k+14],S43,0xAB9423A7);
                b = _II(b,c,d,a,x[k+5],S44,0xFC93A039);
                a = _II(a,b,c,d,x[k+12],S41,0x655B59C3);
                d = _II(d,a,b,c,x[k+3],S42,0x8F0CCC92);
                c = _II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
                b = _II(b,c,d,a,x[k+1],S44,0x85845DD1);
                a = _II(a,b,c,d,x[k+8],S41,0x6FA87E4F);
                d = _II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
                c = _II(c,d,a,b,x[k+6],S43,0xA3014314);
                b = _II(b,c,d,a,x[k+13],S44,0x4E0811A1);
                a = _II(a,b,c,d,x[k+4],S41,0xF7537E82);
                d = _II(d,a,b,c,x[k+11],S42,0xBD3AF235);
                c = _II(c,d,a,b,x[k+2],S43,0x2AD7D2BB);
                b = _II(b,c,d,a,x[k+9],S44,0xEB86D391);
                a = addUnsigned(a,AA);
                b = addUnsigned(b,BB);
                c = addUnsigned(c,CC);
                d = addUnsigned(d,DD);
            }

            var temp = wordToHex(a)+wordToHex(b)+wordToHex(c)+wordToHex(d);
            return temp.toLowerCase();
        }
    });
    // ......................................................................../

})(window);
