/**
 * @desc       Плагин с набором вспомагательных функций
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       w, area, ...
 */
w.helper = {
    url:function(burl,arg,cname,aname) {// cname - контроллер, aname - екшены
        var a = {};
        if(cname) a[w.area.nctrl] = cname;
        if(aname) a[w.area.nacts] = aname;
        for(var i in arg) a[i] = arg[i];
        a = w.http_build_query(a);
        if(!/[?]+/i.test((burl+'')) && (a+'').length > 1) burl += '?';
        else if(!/&$/i.test((burl+'')) && (a+'').length > 1) burl += '&';
        return burl+a;
    },
    // тоже что и url, только строка параметров находится в якоре(после #)
    hurl:function(burl,hash,cname,aname) {
        var a = {};
        if(cname) a[w.area.nctrl] = cname;
        if(aname) a[w.area.nacts] = aname;
        for(var i in hash) a[i] = hash[i];
        if(!/[#]+/i.test((burl+''))) burl += '#';
        return burl.replace(/([^#]*)$/i,w.http_build_query(a));
    },
    /*
     * @link http://phpjs.org/functions/date
     */
    date:function date(format,timestamp) {
        var that = this,
            jsdate, f, formatChr = /\\?([a-z])/gi, formatChrCb,
            _pad = function(n,c) {
                if((n = n + "").length < c) {
                    return new Array((++c)-n.length).join("0") + n;
                } else {
                    return n;
                }
            },
            txt_words = ["Sun","Mon","Tues","Wednes","Thurs","Fri","Satur",
                "January","February","March","April","May","June","July",
                "August","September","October","November","December"],
            txt_ordin = {
                1:"st",
                2:"nd",
                3:"rd",
                21:"st",
                22:"nd",
                23:"rd",
                31:"st"
            };
        formatChrCb = function(t,s) {
            return f[t] ? f[t]() : s;
        };
        f = {
            d:function() {return _pad(f.j(),2);},
            D:function() {return f.l().slice(0,3);},
            j:function() {return jsdate.getDate();},
            l:function() {return txt_words[f.w()] + 'day';},
            N:function() {return f.w() || 7;},
            S:function() {return txt_ordin[f.j()] || 'th';},
            w:function() {return jsdate.getDay();},
            z:function() {
                var a = new Date(f.Y(),f.n()-1,f.j()),
                    b = new Date(f.Y(),0,1);
                return Math.round((a-b)/864e5)+1;
            },
            W:function() {
                var a = new Date(f.Y(),f.n()-1,f.j()-f.N()+3),
                    b = new Date(a.getFullYear(),0,4);
                return 1 + Math.round((a-b)/864e5/7);
            },
            F:function() {return txt_words[6+f.n()];},
            m:function() {return _pad(f.n(),2);},
            M:function() {return f.F().slice(0,3);},
            n:function() {return jsdate.getMonth()+1;},
            t:function() {return (new Date(f.Y(),f.n(),0)).getDate();},
            L:function() {return new Date(f.Y(),1,29).getMonth() === 1 | 0;},
            o:function() {
                var n = f.n(), W = f.W(), Y = f.Y();
                return Y + (n === 12 && W < 9 ? -1 : n === 1 && W > 9);
            },
            Y:function() {return jsdate.getFullYear();},
            y:function() {return (f.Y()+"").slice(-2);},
            a:function() {return jsdate.getHours() > 11?"pm":"am";},
            A:function() {return f.a().toUpperCase();},
            B:function() {
                var H = jsdate.getUTCHours()*36e2, // Hours
                    i = jsdate.getUTCMinutes()*60, // Minutes
                    s = jsdate.getUTCSeconds(); // Seconds
                return _pad(Math.floor((H+i+s+36e2)/86.4)%1e3,3);
            },
            g:function() {return f.G()%12 || 12;},
            G:function() {return jsdate.getHours();},
            h:function() {return _pad(f.g(),2);},
            H:function() {return _pad(f.G(),2);},
            i:function() {return _pad(jsdate.getMinutes(),2);},
            s:function() {return _pad(jsdate.getSeconds(),2);},
            u:function() {return _pad(jsdate.getMilliseconds()*1000,6);},
            e:function() {throw 'Not supported (see source code of date() for timezone on how to add support)';},
            I:function() {
                var a = new Date(f.Y(),0), // Jan 1
                    c = Date.UTC(f.Y(),0), // Jan 1 UTC
                    b = new Date(f.Y(),6), // Jul 1
                    d = Date.UTC(f.Y(),6); // Jul 1 UTC
                return 0+((a-c) !== (b-d));
            },
            O:function() {
                var a = jsdate.getTimezoneOffset();
                return (a > 0?"-":"+")+_pad(Math.abs(a/60*100),4);
            },
            P:function() {
                var O = f.O();
                return (O.substr(0,3)+":"+O.substr(3,2));
            },
            T:function() {return 'UTC';},
            Z:function() {return -jsdate.getTimezoneOffset()*60;},
            c:function() {return 'Y-m-d\\Th:i:sP'.replace(formatChr,formatChrCb);},
            r:function() {return 'D, d M Y H:i:s O'.replace(formatChr,formatChrCb);},
            U:function() {return jsdate.getTime()/1000 | 0;}
        };
        this.date = function(format,timestamp) {
            that = this;
            jsdate = (
                (typeof timestamp === 'undefined')?new Date() : // Not provided
                (timestamp instanceof Date)?new Date(timestamp) : // JS Date()
                new Date(timestamp*1000) // UNIX timestamp (auto-convert to int)
            );
            return format.replace(formatChr,formatChrCb);
        };
        return this.date(format,timestamp);
    },
    diffDate1:function(date1,date2) {
        var dm = {1:31,2:28,3:31,4:30,5:31,6:30,7:31,8:31,9:30,10:31,11:30,12:31};
        var d = {d1:{},d2:{}};
        
        var D = new Date(date1);
        d.d1.year = D.getFullYear();
        d.d1.month = D.getMonth() + 1;
        d.d1.date = D.getDate();
        d.d1.time = D.getTime();

        D = new Date(date2);
        d.d2.year = D.getFullYear();
        d.d2.month = D.getMonth() + 1;
        d.d2.date = D.getDate();
        d.d2.time = D.getTime();

        var f = function(t1,t2) {// t1 - t2;
            var res = {year:0,month:0,date:0};
            
            var data9 = t1.date;
            while(data9 < t2.date) {
                var m = ((t1.month-1)<1)?12:(t1.month-1);
                var v = (t1.year/4==Math.floor(t1.year/4) && m==2)?1:0;
                data9 = (t1.date+dm[m]+v);
                t1.month = t1.month - 1;
            }
            res.date = data9 - t2.date;
            
            if(t1.month < t2.month) {
                res.month = (t1.month+12) - t2.month;
                t1.year = t1.year - 1;
            } else {
                res.month = t1.month - t2.month;
            }
            res.year = t1.year - t2.year;            
            return res;
        }

        if(d.d1.time > d.d2.time) {
            return f(d.d1,d.d2);
        } else if(d.d1.time < d.d2.time) {
            return f(d.d2,d.d1);
        } else {
            return {year:0,month:0,date:0};
        }
    }
}
