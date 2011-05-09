/**
 * @desc       Система шаблонирования
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       w
 */
(function(w) {// сделать обработку ошибок при генерации функции!!!

    var cache = {}, defaultSett = {
        debug:true,
        cache:true,
        arg:{data:null,l10n:null}
    }

    w.fn.extend({
        template:function(options) {
            var $this = this;
            var id = $this.id?$this.id:null;
            if(!id) return 'UNKNOWN ELEMENT ID';
            var opt = w.extend(defaultSett,options || {});
            // подготовка аргументов
            var key = [], value = [];
            if(typeof(opt.arg) == 'object') {
                for(var i in opt.arg) {
                    key.push(i);
                    value.push(opt.arg[i]);
                }
            }
            var res = '';
            if(opt.cache) {
                if(cache[id]) {
                    res = cache[id];
                } else {
                    var f = compiler(opt.debug,$this,key);
                    cache[id] = f;
                    res = f;
                }
            } else {
                var f1 = compiler(opt.debug,$this,key);
                res = f1;
            }
            return res.apply(null,value);
        }
    });

    function compiler(debug,el,keys) {
        var tpl = el.innerHTML;
        var f = null;
        if(debug) {
            f = parser(tpl,keys);
        } else {
            try {
                f = parser(tpl,keys);
            } catch(e) {
                f = function() {
                    return e;
                }
            }
        }
        return f;
    }

    function parser(data,keys) {
        var i;
        var VAR = 'V_'+w.md5((new Date()).getTime()+''), TMP = w.md5((new Date()).getTime()+'=REPLACE');
        // 1 - строки с тегами заменяем специальными маркерами
        var bytecode = data+'';
        var s1 = false, s2 = false, strc = {}, t = 0;

        var bt = bytecode;
        bt = bt.replace(/\\'/g,'~~');
        bt = bt.replace(/\\"/g,'~~');
        
        for(i=0; i<bt.length; i++) {
            if(bt[i] == '\'') {
                if(s2) continue;
                if(!s1) {
                    strc[t] = i;
                    s1 = true;
                } else {
                    strc[t] = [strc[t],i];
                    t++;
                    s1 = false;
                }
            } else if(bt[i] == '"') {
                if(s1) continue;
                if(!s2) {
                    strc[t] = i;
                    s2 = true;
                } else {
                    strc[t] = [strc[t],i];
                    t++;
                    s2 = false;
                }
            }
        }

        for(i in strc) {
            var str0 = bytecode.substr(strc[i][0],strc[i][1]?(strc[i][1]-strc[i][0]):bytecode.length);
            var str1 = str0;
            str1 = str1.replace('<%=','~|wqss|~');
            str1 = str1.replace('<%','~|wqs|~');
            str1 = str1.replace('%>','~|wqf|~');
            if(str0 != str1) {
                bytecode = bytecode.replace(str0,TMP);
                bytecode = bytecode.replace(TMP,str1);
            }
        }

        ///alert(bytecode);
        
        // 2 - производим преобразование html и js
        bytecode = bytecode.split('<%=');
        for(i in bytecode) {
            if(i == 0) continue;
            var s = bytecode[i].split('%>');
            //s[0] - js, s[1]-s[n] - html;
            var s1 = '';
            for(var j=0; j<s.length; j++) {
                if(j == 0) continue;
                s1 = s1+'%>'+s[j];
            }
            var s0 = s[0].replace(/(;+)\s*$/g,'');
            bytecode[i] = '<% '+VAR+'.push('+s0+'); '+s1;
        }
        bytecode = bytecode.join('');

        //alert(bytecode);

        bytecode = bytecode.split('<%');
        for(var i in bytecode) {
            var html = bytecode[i].split('%>');
            if(html[1] === undefined) {
                bytecode[i] = VAR+'.push(\''+html[0]+'\');';
            } else if(html[1] !== undefined) {
                bytecode[i] = html[0]+';'+VAR+'.push(\''+html[1]+'\');';
            }
        }
        bytecode = bytecode.join("\n");

        // обратные преобразования строк
        bytecode = bytecode.replace('~|wqss|~','<%=');
        bytecode = bytecode.replace('~|wqs|~','<%');
        bytecode = bytecode.replace('~|wqf|~','%>');

        bytecode = bytecode.replace(/(\\|\n|\f|\r|\t)/g,'\\$1');
        bytecode = bytecode.replace(/\"/g,'\\"');

        //alert(bytecode);

        var f1 = eval('new Function("'+(keys && (typeof(keys.join) == 'function')?keys.join(','):'')+'","var '+VAR+' = []; '+bytecode+' return '+VAR+'.join(\\"\\");");');

        //alert(f1);

        return f1;
    }

    function quoteescape(str,q,qs) {
        var data = [], s = false, i = 0;
        for(i=0; i<str.length; i++) {
            data.push(str[i]);
        }
        
        for(i=0; i<data.length; i++) {
            if(data[i] == q && data[(i-1)<0?0:(i-1)] != '\\' && data[(i-2)<0?0:(i-2)] != '\\') {
                if(!s) {
                    s = true;
                } else {
                    if(data[(i-1)<0?0:(i-1)] != '\\') s = false;
                }
            }
            if(data[i] == qs && s == true) {
                if(data[(i-1)<0?0:(i-1)] != '\\' && data[(i-2)<0?0:(i-2)] != '\\') data[i] = '\\\\'+data[i];
                else if(data[(i-1)<0?0:(i-1)] != '\\') data[i] = '\\'+data[i];
            }
        }

        return data.join('');
    }

    

})(W);
