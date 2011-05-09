/**
 * @desc       captcha основанная на оптической иллюзии
 * @package    w framework
 * @category   engine-client
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       w
 */
(function(w) {

    var defaultSett = {
        res:null,
        pClass:null,
        fclick:null,
        path:null,
        width:null,
        height:null
    };

    w.fn.extend({
        ecaptcha:function(options) {
            var $this = this;// тег с тестом Тьюринга
            var opt = w.extend(defaultSett,options || {});

            // очистка тега контейнера
            for(var i in $this.childNodes) {
                try {$this.removeChild($this.childNodes[i]);} catch(E) {}
            }

            // создать тег
            var img = w.window.document.createElement('img');
            img.setAttribute('src',opt.path);
            img.border = 0;
            if(opt.width) img.width = opt.width;
            if(opt.height) img.height = opt.height;
            $this.appendChild(img);
            $this.style.position = 'relative';
            
            w(img).follow('click',function(e) {
                var offset = getOffset(e.target);
                var x = e.pageX - offset.left;
                var y = e.pageY - offset.top;

                var els = $this.children;
                for(var i in els) {
                    if(typeof(els[i]) != 'object' || els[i].getAttribute('data-type') != 'p') continue;
                    $this.removeChild(els[i]);
                }

                // создать новую точку
                var dp = w.window.document.createElement('div');
                if(opt.pClass) dp.setAttribute('class',opt.pClass);
                dp.setAttribute('data-type','p');
                dp.style.position = 'absolute';
                $this.appendChild(dp);
                // отцентровка точки
                var size = pSize(dp);
                dp.style.top = Math.round(y-size.h/2)+'px';
                dp.style.left = Math.round(x-size.w/2)+'px';

                // заносить параметры в спец переменную
                if(opt.res) {
                    w(opt.res).val({x:x,y:y});
                }
                if(typeof(opt.fclick) == 'function') opt.fclick();
                return false; // fix ie
            });
        }
    });

    function pSize(p) {
        var m = {
            top:((((typeof(getComputedStyle) == 'function') && getComputedStyle(p,null).marginTop) || (p.currentStyle && p.currentStyle.marginTop))+'').replace(/([^0-9\.,]+)/ig,''),
            left:((((typeof(getComputedStyle) == 'function') && getComputedStyle(p,null).marginLeft) || (p.currentStyle && p.currentStyle.marginLeft))+'').replace(/([^0-9\.,]+)/ig,''),
            bottom:((((typeof(getComputedStyle) == 'function') && getComputedStyle(p,null).marginBottom) || (p.currentStyle && p.currentStyle.marginBottom))+'').replace(/([^0-9\.,]+)/ig,''),
            right:((((typeof(getComputedStyle) == 'function') && getComputedStyle(p,null).marginRight) || (p.currentStyle && p.currentStyle.marginRight))+'').replace(/([^0-9\.,]+)/ig,'')
        }
        return {h:parseFloat(p.offsetHeight || 0)+parseFloat(m.top || 0)+parseFloat(m.bottom || 0),
            w:parseFloat(p.offsetWidth || 0)+parseFloat(m.left || 0)+parseFloat(m.right || 0)};
    }

    /*
     * @link http://javascript.ru/ui/offset
     */
    function getOffset(elem) {
        if (elem.getBoundingClientRect) {
            return getOffsetRect(elem)
        } else {
            return getOffsetSum(elem)
        }
    }

    function getOffsetSum(elem) {
        var top=0, left=0
        while(elem) {
            top = top + parseInt(elem.offsetTop)
            left = left + parseInt(elem.offsetLeft)
            elem = elem.offsetParent
        }
        return {top:top,left:left};
    }

    function getOffsetRect(elem) {
        var box = elem.getBoundingClientRect();
        var body = w.window.document.body;
        var docElem = w.window.document.documentElement;
        var scrollTop = w.window.pageYOffset || docElem.scrollTop || body.scrollTop;
        var scrollLeft = w.window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
        var clientTop = docElem.clientTop || body.clientTop || 0;
        var clientLeft = docElem.clientLeft || body.clientLeft || 0;
        var top  = box.top +  scrollTop - clientTop;
        var left = box.left + scrollLeft - clientLeft;
        return {top:Math.round(top),left:Math.round(left)};
    }

})(W);
