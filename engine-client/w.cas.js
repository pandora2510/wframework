/**
 * @desc       Плагин для работы с формами
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       w, ahr
 */
(function(w) {// добавить полноценную работу с checkbox и radio!!!
              // проблема с двойными, динамическими iframe

    var defaultSett = {
        fieldEvent:'blur',// blur|change
        liveValidator:true,
        action:w.window.location.href,
        method:'POST',// post|get|files
        enctype:'application/x-www-form-urlencoded',// application/x-www-form-urlencoded|multipart/form-data
        fields:{},//{name:{rule:{arg:null,error:null|function(el,nrule),truth:null|function(el,nrule)},rule1:{}},name1:{}}
        submit:null,// w selector|[w selector,...]
        reset:null,// w selector|[w selector,function()]
        success:null,// function(data,st)
        error:null,// function(data,st)
        open:null,// function() // перенести open при files!!!
        complete:null, // function(data,st)
        ferror:null,// function(el,nrule)
        ftruth:null,// function(el,nrule)
        ffocusin:null,// function(el)
        ffocusout:null,// function(el)
        fwait:null,// function(el)
        param:null // {page,secure,user,pass,autoreload,files(cont)} - дополнительные параметры
    };

    w.fn.extend({
        cas:function(options) {// ff,opera - лишние страницы в истории
            var $this, opt = {}, process;
            $this = this;
            opt = w.extend(defaultSett,options || {});

            if(($this.nodeName+'').toUpperCase() == 'FORM') {
                w($this).follow('submit',function(){return false;}); // предотвращаем отправку формы
            }
            opt.method = (opt.method+'').toUpperCase();
            if(opt.method != 'GET' && opt.method != 'POST' && opt.method != 'FILES') opt.method = 'GET';

            var ev = (opt.fieldEvent+'').toLowerCase();
            if(ev != 'blur' && ev != 'change') opt.fieldEvent = 'blur';

            var query = function(d) {
                var key = (opt.method+'').toLowerCase()=='files'?true:false;
                if(!opt.param || typeof(opt.param) != 'object') opt.param = {};
                return w.ahr({
                    secure:(typeof(opt.param['secure']) != 'undefined')?opt.param['secure']:false,
                    user:(typeof(opt.param['user']) != 'undefined')?opt.param['user']:null,
                    pass:(typeof(opt.param['pass']) != 'undefined')?opt.param['pass']:null,
                    autosend:key?false:true,
                    type:'json',
                    method:opt.method,
                    url:opt.action,
                    cache:false,
                    headers:{'Content-Type':opt.enctype},
                    data:key?((typeof(opt.param['page']) != 'undefined')?opt.param['page']:'about:blank'):d,
                    success:function(data,st) {
                        if(typeof(opt.success) == 'function') opt.success(data,st);
                    },
                    error:function(xhr,st) {
                        if(typeof(opt.error) == 'function') opt.error(xhr.responseText,st);
                    },
                    complete:function(xhr,st) {
                        if(typeof(opt.complete) == 'function') opt.complete(xhr.responseText,st);
                        process = false;
                        if(!opt.param || typeof(opt.param) != 'object') opt.param = {}
                        if(opt.param['autoreload']) ff();
                    },
                    open:function() {
                        if(typeof(opt.open) == 'function' && ((opt.method+'').toUpperCase() != 'FILES')) opt.open();
                        return true;
                    }
                });
            };

            var check = new validate(), xhr = null;

            var fv = function() {
                if(opt.liveValidator) validator(true,check,$this,opt);
            },
            fd = function(init){
                var arr = w.getAll($this,'input,textarea,select');
                for(var i in arr) {
                    var el = arr[i];
                    if(!el.type) continue;
                    if(el.disabled || el.type == 'reset' || el.type == 'button' || el.type == 'image' || el.type == 'file') continue;
                    var attr = el.getAttribute('data-default');
                    if(el.type == 'select-multiple') {// multiple!!!
                        if(attr) w(el).val(attr.split(','));else if(!init) w(el).val([0]);
                    } else {
                        if(attr) w(el).val(attr);else if(!init) w(el).val('');
                    }
                }
            },
            ff = function() {
                if((opt.method+'').toLowerCase() == 'files') {
                    if(xhr && typeof(xhr) == 'object' && typeof(xhr.about) == 'function') xhr.about();
                    xhr = query();
                    if(typeof(opt.param['files']) == 'function') opt.param['files'](xhr.xhr.parentNode);
                    w(xhr.xhr).follow('load',function(e) {
                        fv();
                        fd(true);
                    });
                } else {
                    fv();
                    fd(true);
                }
            }

            // базовая функция
            ff();
            
            // default reset
            var fsubmit = function(e) {
                if(process) return; else process = true;
                // формирование блока данных                
                var f = validator(false,check,$this,opt);
                if((opt.method+'').toLowerCase() == 'files') {
                    // отправить форму
                    if(!f) {
                        process = false;
                        return undefined;
                    } else {
                        // доотправить форму
                        if(typeof(opt.open) == 'function' && ((opt.method+'').toUpperCase() == 'FILES')) opt.open();
                        // добавить submit
                        var doc = xhr.xhr.contentDocument || xhr.xhr.contentWindow.document || xhr.xhr.document;
                        for(var i=0; i<doc.forms.length; i++) {
                            var s = doc.forms[i].submit;
                            var sub = doc.createElement('input');
                            sub.type = 'hidden';
                            sub.name = e.target.name;
                            sub.value = e.target.value;
                            doc.forms[i].appendChild(sub);
                            if(typeof(doc.forms[i].submit) != 'function') doc.forms[i]['data-form-add-submit'] = s;
                        }

                        xhr.query();
                    }
                } else {
                    if(!f) {
                        process = false;
                        return undefined;
                    } else {
                        f[e.target.name] = w(e.target).val();
                        xhr = query(f);
                    }
                }
            };

            if(typeof(opt.reset) == 'object' && opt.reset && typeof(opt.reset.pop) == 'function') {
                w(opt.reset[0]).follow('click',function() {
                    fd();
                    if(typeof(opt.reset[1]) == 'function') opt.reset[1]();
                });
                if(typeof(opt.reset[1]) == 'function') opt.reset[1]();// первый вызов reset
            } else {
                w(opt.reset).follow('click',function(e) {fd();});
            }

            // сделать несколько сабмитов
            if(typeof(opt.submit) == 'object' && opt.submit && typeof(opt.submit.pop) == 'function') {
                for(var j in opt.submit) {
                    w(opt.submit[j]).follow('click',fsubmit);
                }
            } else {
                w(opt.submit).follow('click',fsubmit);
            }
        }        
    });

    w.extend({
        setupCas:function(options) {
            defaultSett = w.extend(defaultSett,options || {});
        }
    });

    function validator(live,Validate,$this,opt) {// live - Что делает функция!! true/false
        var fields = {}, v = true; // name:value

        var f = function(el1,live1) {
            var name = (el1.name+'').split('[')[0];
            for(var i in opt.fields[name]) {
                if(typeof(opt.fields[name][i]) != 'object' || !opt.fields[name][i]) opt.fields[name][i] = {};
                var rule = i, arg = opt.fields[name][i]['arg']?opt.fields[name][i]['arg']:{};
                if(!live1 && i == 'remote') continue;// избежание лишних запросов
                // добавлять до arg параметры, если rule == 'remote'
                //if(i == 'remote') {
                //    if(typeof(opt.param['open']) == 'function') arg['open'] = opt.param['open'];
                //    if(typeof(opt.param['complete']) == 'function') arg['complete'] = opt.param['complete'];
                //}
                var r = undefined;
                if(typeof(Validate[rule]) != 'function') {
                    alert('Validator - "'+rule+'" - NOT FOUND');
                } else {
                    var val = w(el1).val();
                    if(el1.type == 'select-multiple' && !val) val = [];
                    if(typeof(val) == 'object') {
                        var r0 = true;r = true;
                        for(var j in val) {
                            r0 = Validate[rule](val[j],arg);
                            if(!r0) r = r0;
                        }
                    } else {
                        r = Validate[rule](val,arg);
                    }
                }
                if(r === false) {
                    if(typeof(opt.fields[name][i]['error']) == 'function') {
                        opt.fields[name][i]['error'](el1,rule);
                    } else if(typeof(opt.ferror) == 'function') {
                        opt.ferror(el1,rule);
                    }
                    v = false;
                    break; // выход из цыкла по первому сообщению об ошибке
                } else if(r === true) {
                    if(typeof(opt.fields[name][i]['truth']) == 'function') {
                        opt.fields[name][i]['truth'](el1,rule);
                    } else if(typeof(opt.ftruth) == 'function') {
                        opt.ftruth(el1,rule);
                    }
                } else {
                    if(typeof(opt.fwait) == 'function') opt.fwait(el1);
                }
            }
        }
        
        var arr = w.getAll($this,'input,textarea,select');
        for(var i in arr) {// работа с radio не реализована
            var el = arr[i];
            if(typeof(el) != 'object') continue;
            if(el.disabled || el.type == 'reset' || el.type == 'submit' || el.type == 'button' || el.type == 'image') continue;
            var name = (el.name+'').split('[')[0];

            // focus
            w(el).follow('focus',function(e) {
                if(typeof(opt.ffocusin) == 'function') opt.ffocusin(e.target);
            });
            // checkbox
            if(el.type == 'checkbox' || el.type == 'radio') {
                w(el).follow('click',function(e) {
                    if(typeof(opt.ffocusin) == 'function') opt.ffocusin(e.target);
                });
            }

            if(opt.fields[name] && typeof(opt.fields[name]) == 'object') {
                if(live) {
                    w(el).follow(opt.fieldEvent,function(e) {
                        f(e.target,true);
                    });                    
                } else {
                    f(el,false);
                }
            }
            // возникает на всех элементах
            if(typeof(opt.ffocusout) == 'function') {
                w(el).follow('blur',function(e) {opt.ffocusout(e.target)});
            }
            
            // + поддержка автомассивов
            if(!live && (opt.method+'').toLowerCase() != 'files') {
                name = (el.name+'');
                if(fields[name]) {
                    if(fields[name] instanceof Array) {
                        fields[name].push(w(el).val());
                    } else {
                        var arg = [];
                        arg.push(fields[name],w(el).val());
                        fields[name] = arg;
                    }
                } else {
                    fields[name] = w(el).val();
                }
            }
        }
        return v?fields:v;
    }

    // валидаторы
    function validate() {
        return {
            nec:function(val) {
                return (val+'').length > 0;
            },
            regex:function(val,arg) {
                return (new RegExp(arg)).test(val);
            },
            length:function(val,arg) {
                return (val+'').length == arg;
            },
            minlength:function(val,arg) {
                return (val+'').length >= arg;
            },
            maxlength:function(val,arg) {
                return (val+'').length <= arg;
            },
            // http://docs.jquery.com/Plugins/Validation/Methods/email
            // contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
            email:function(val) {
                return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(val);
            },
            // http://docs.jquery.com/Plugins/Validation/Methods/url
            // contributed by Scott Gonzalez: http://projects.scottsplayground.com/iri/
            url:function(val) {
                return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(val);
            },
            // http://docs.jquery.com/Plugins/Validation/Methods/dateISO
            dateISO:function(val) {
                return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(val);
            },
            // http://docs.jquery.com/Plugins/Validation/Methods/number
            number:function(val) {
                return /^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(val);
            },
            range:function(val,arg) {
                if(val == arg) return true;
                if(typeof(arg) != 'object') return false;
                for(var i in arg) {
                    if(val == arg[i]) return true;
                }
                return false;
            },
            // http://docs.jquery.com/Plugins/Validation/Methods/accept
            accept:function(val,arg) {
                return (new RegExp(".("+(typeof(arg)=="string"?arg.replace(/,/g,'|'):"png|jpe?g|gif")+")$","i")).test(val);
            },
            funct:function(val,f) {
                var st = false;
                if(typeof(f) == 'function') st = f(val);
                return st===false?false:(st==true?true:false);
            },
            remote:function(val,arg) {// arg: url,open,complete,name, success
                var obj = {};
                obj[arg.name] = val;
                w.ahr({
                    type:'json',
                    method:'GET',
                    url:arg.url,
                    cache:false,
                    data:obj,
                    open:function() {
                        if(typeof(arg.open) == 'function') arg.open();
                        return true;
                    },
                    complete:arg.complete,
                    success:arg.success,
                    autosend:true
                });
                return undefined;
            }
        }
    }

})(W);
