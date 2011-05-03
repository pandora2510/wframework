/**
 * @desc       ajax
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       w
 * @version    0.2.8
 */
(function(w) {

    var defaultSett = {
        secure:false,
        xhr:null,
        method:'GET',// GET,POST,FILES
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        data:null,// page при FILES
        url:w.window.location.href,
        type:'json',// всегда json
        cache:false,
        user:null,
        pass:null,
        error:null,
        success:null,
        open:null,
        progress:null,
        complete:null,
        autosend:true // вызывает отправку данных
    };

    w.extend({
        ahr:function(options) {
            var opt = w.extend(defaultSett,options || {});
            // 200 <= x < 300, 304
            opt.method = (opt.method+'').toUpperCase();
            if(opt.method != 'GET' && opt.method != 'POST' && opt.method != 'FILES') opt.method = 'GET';

            // требования к браузерам при https!!!
            var scheme = w.window.location.protocol.toUpperCase();
            scheme = scheme.replace(/[^htpsHTPS]*$/i,'');
            if(scheme == 'HTTPS') opt.url = (opt.url+'').replace(/^http:\/\//i,'https://');

            var xhr = createXHR(opt);
            if(typeof(opt.open) == 'function') {
                if(!opt.open(xhr,opt)) {
                    xhr.abort();
                    return xhr;
                }
            } else {
                xhr.abort();
            }

            if(opt.data) {
                if(opt.method == 'POST' && opt.secure) {
                    if(typeof(opt.data) == 'string') {
                        var a = {};
                        w.parse_str(opt.data,a);
                        opt.data = a;
                    }
                } else if(opt.method == 'GET' || opt.method == 'POST') {
                    if(typeof(opt.data) == 'object') opt.data = w.http_build_query(opt.data);
                }
            }
            if(opt.method == 'GET' && opt.data) {
                if(!/[?]+/i.test(opt.url)) opt.url += '?';
                if(!/[?&]+$/i.test(opt.url)) opt.url += '&';
                opt.url += opt.data;
            }
            if(!opt.cache) {
                if(!/[?]+/i.test(opt.url)) opt.url += '?';
                if(!/[?&]+$/i.test(opt.url)) opt.url += '&';
                opt.url += '_='+(new Date()).getTime();
            }
            
            if(opt.user) xhr.open(opt.method,opt.url,true,opt.user,opt.pass);
             else xhr.open(opt.method,opt.url,true);

            xhr.onreadystatechange = function() {
                if(typeof(opt.progress) == 'function') opt.progress(xhr,xhr.readyState);
                if(xhr.readyState == 4) {
                    if(xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) {
                        var data;
                        if(opt.type == 'json') {
                            data = w.json_decode(xhr.responseText);
                            if(data === null) {
                                xhr.statusText = 'parsererror';
                                if(typeof(opt.error) == 'function') opt.error(xhr,xhr.statusText,{});
                                if(typeof(opt.complete) == 'function') opt.complete(xhr,xhr.statusText);
                                return;
                            }
                        }
                        xhr.statusText = 'success';
                        if(typeof(opt.success) == 'function') opt.success(data,xhr.statusText,xhr);
                    } else {
                        xhr.statusText = 'error';
                        if(typeof(opt.error) == 'function') opt.error(xhr,xhr.statusText,{});
                    }
                    if(typeof(opt.complete) == 'function') opt.complete(xhr,xhr.statusText);
                }
            }
            for(var i in opt.headers) {
                if(opt.method == 'GET' && /content-type/i.test(i)) continue;
                xhr.setRequestHeader(i,opt.headers[i]);
            }

            var ff1 = function() {
                xhr.send(opt.method=='POST'?opt.data:null);
            }
            
            // send
            if(opt.autosend) ff1(); else xhr.query = ff1;

            return xhr;
        },
        setupAhr:function(options) {
            defaultSett = w.extend(defaultSett,options || {});
        }
    });

    function createXHR(opt) {        
        if(opt.xhr) return opt.xhr;
        if(opt.secure || opt.method == 'FILES') return new ixhr(opt.data); else return new dxhr();
    }

    var ixhr = function(page) {
        var self, header = {}, mtd, url, ifr, doc, q = 0;
        return {
            abort:function() {
                header = {};
                w(ifr).ufollow('load');
                try {ifr.parentNode.parentNode.removeChild(ifr.parentNode);} catch(E) {}
            },
            getAllResponseHeaders:function() {
                return null;
            },
            getResponseHeader:function(headerName) {
                return null;
            },
            setRequestHeader:function(label,value) {
                header[label] = value;
            },
            open:function(method,URL,async,userName,password) {
                self = this;
                mtd = method.toUpperCase();
                url = URL;
                if(mtd != 'GET' && mtd != 'POST' && mtd != 'FILES') mtd = 'GET';

                // 443 jsonsecure
                if(!/[?]+/i.test(url)) url += '?';
                if(!/[?&]+$/i.test(url)) url += '&';
                url += w.http_build_query({"X-Requested-With":"XMLHttpRequest"});// идинтификация асинхронного запроса

		var scheme = w.window.location.protocol.toUpperCase();
		scheme = scheme.replace(/[^htpsHTPS]*$/i,'');
		page = (scheme=='HTTPS')?(page+'').replace(/^http:\/\//i,'https://'):page;
                
                ifr = createIFR(mtd,page);
                this.xhr = ifr;
                ifr.done = false;
                doc = ifr.contentDocument || ifr.contentWindow.document || ifr.document;

                w(ifr).follow('load',function(e) {// следить сколько раз срабатывает!!!
                    if(!ifr.done) return undefined; // защита от повторного срабативания при FILES
                    ifr.done = false;
                    doc = ifr.contentDocument || ifr.contentWindow.document || ifr.document;
                    var f = function() {
                        self.readyState = 3;
                        if(typeof(self.onreadystatechange) == 'function') self.onreadystatechange();
                        // заполняются переменные ответа
                        self.status = doc.body.getAttribute('data-status') || 666;
                        self.statusText = self.status==200?'Ok':'666';
                        try {self.responseText = doc.body.childNodes[0].innerHTML || doc.body.innerHTML;} catch(e) {}
                        self.readyState = 4;
                        // вызов onreadystatuschange
                        if(typeof(self.onreadystatechange) == 'function') self.onreadystatechange();
                        self.abort();
                        // исправлять ошибки навигации
                        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        // opera, ff3 при FILES на 1 назад!!!
                        // hst(mtd);
                    }
                    var id = setInterval(function() {
                        if(doc.body) {
                            clearInterval(id);
                            f();
                        }
                    },50);
                    //if(!ifr.done) return undefined; // защита от повторного срабативания
                    //ifr.done = false;
                    //ifr.done = true;
                });

                this.readyState = 1;
                if(typeof(this.onreadystatechange) == 'function') this.onreadystatechange();
            },
            send:function(content) {
                // запуск таймера на onreadystatechange
                if(!content) content = {};
                self = this;

                doc = ifr.contentDocument || ifr.contentWindow.document || ifr.document;

                if(mtd == 'GET') {
                    ifr.setAttribute('src',url);
                    ifr.done = true;
                } else if(mtd == 'POST') {
                    var f = function() {
                        doc.body.innerHTML = '';
                        var form = doc.createElement('form');
                        form.setAttribute('method','POST');
                        form.setAttribute('enctype',header['Content-Type']);
                        form.setAttribute('action',url);
                        if(typeof(content) != 'object') {
                            var arr = {};
                            w.parse_str(content,arr);
                            content = arr;
                        }
                        addPOSTData(content,doc,form);
                        doc.body.appendChild(form);

                        ifr.done = true;

                        form.submit();
                    }
                    
                    var id = setInterval(function() {// некоторые браузеры очень сильно тупят
			if(doc.body) {
                            clearInterval(id);
                            f();
			}
                    },50);

                } else if(mtd == 'FILES') {
                    var f1 = function() {
			if(w.browser.n == 'msie') {
                            var fs = doc.forms;
                            if(fs.length == 0) return 'NOT';
                            for(var j=0; j<doc.forms.length; j++) {
                                if(j == 0) continue;
				doc.forms[j].parentNode.removeChild(doc.forms[j]);
                            }
                            fs = doc.forms[0];
                            fs.setAttribute('method','POST');
                            fs.setAttribute('enctype',header['Content-Type']);
                            fs.setAttribute('action',url);

                            ifr.done = true;

                            if(typeof(fs['data-form-add-submit']) == 'function') fs['data-form-add-submit'].call(fs);
                            else fs.submit();
                            
                            return true;
			}
					
                        var form = doc.createElement('form');
			form.setAttribute('enctype',header['Content-Type']);
                        form.setAttribute('method','POST');                        
                        form.setAttribute('action',url);

                        var s = form.submit;

                        // перемещение input="file"
                        var files = w.getAll(doc.body,'input,select,textarea');
                        for(var i=0; i<files.length; i++) {
                            form.appendChild(files[i]);
                        }

                        doc.body.appendChild(form);
						
                        ifr.done = true;

                        s.call(form);
						
			return true;
                    };
                    var id1 = setInterval(function() {// некоторые браузеры очень сильно тупят
			if(doc.body) {
                            clearInterval(id1);
                            if(f1() == 'NOT') throw new Error("form NOT FOUND");
			}
                    },50);
                }
                this.readyState = 2;
                if(typeof(this.onreadystatechange) == 'function') this.onreadystatechange();
            },
            onreadystatechange:null,
            readyState:0,
            responseText:null,
            responseXML:null,
            status:null,
            statusText:null,
            xhr:null
        };

        function createIFR(mtd,page) {
            page = (mtd == 'FILES')?page:'about:blank';
            return w.createIFrame(w.window.document.body,page,false);
        }

        function addPOSTData(data,doc,form,prefix) {
            for(var i in data) {
                var key = i;
                if(prefix) key = key+'['+i+']'; // проверить нужны ли кавычки
                if(typeof(data[i]) == 'object') {
                    addPOSTData(data[i],doc,form,key);
                } else {
                    var el = doc.createElement('input');
                    el.name = key;
                    el.type = 'text';
                    el.value = data[i];
                    form.appendChild(el);
                }
            }
        }
    };

    var dxhr = function() {
        var self, xhr;
        try {
            xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                xhr = false;
            }
        }
        if (!xhr && typeof XMLHttpRequest!='undefined') {
            xhr = new XMLHttpRequest();
        }
        
        xhr.onreadystatechange = function() {
            self.readyState = xhr.readyState;
            try {
		self.status = xhr.status;
                self.statusText = xhr.statusText,
                self.responseText = xhr.responseText;
                self.responseXML = xhr.responseXML;
	    } catch(E) {}
            if(typeof(self.onreadystatechange) == 'function') self.onreadystatechange();
        }
        return {
            abort:function() {
                xhr.abort();
            },
            getAllResponseHeaders:function() {
                return xhr.getAllResponseHeaders();
            },
            getResponseHeader:function(headerName) {
                return xhr.getResponseHeader(headerName);
            },
            setRequestHeader:function(label,value) {
                xhr.setRequestHeader(label,value);
            },
            open:function(method,URL,async,userName,password) {
                self = this;
                
                this.xhr = xhr;

                xhr.open(method,URL,async,userName,password);
                xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");// индификация асинхронного запроса
                
                this.readyState = 1;
                if(typeof(this.onreadystatechange) == 'function') this.onreadystatechange();
            },
            send:function(content) {
                return xhr.send(content?content:null);
                this.readyState = 2;
                if(typeof(this.onreadystatechange) == 'function') this.onreadystatechange();
            },
            onreadystatechange:null,
            readyState:0,
            responseText:null,
            responseXML:null,
            status:null,
            statusText:null,
            xhr:null
        };
    }

})(W);
