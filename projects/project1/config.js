obj = {
    namePlg:'area',
    paths:[
        'w.js',
        'w.ahr.js',
        'w.area.js',
        'w.template.js',
        'w.cas.js',
        'w.ecaptcha.js',
        'w.helper.js'
    ],
    sufixName:'action-',
    pathName:'index.php?PRJ=project1',
    appFolder:'applications',
    noView:function(path,id) {
        
    },
    aError:function(data,st) {
        
    },
    aTIError:function(data,st) {
        
    },
    open:function() {
        
    },
    complete:function() {
        
    },
    oldbrowser:{
        visibility:true,
        id:'old-browser-warning',
        class_body:'body',
        class_head:'head',
        class_close:'close'
    },
    unknownbrowser:{
        visibility:true,
        id:'unknown-browser-warning',
        class_body:'body',
        class_head:'head',
        class_close:'close'
    },
    notcookie:{
        visibility:true,
        id:'not-cookie-warning',
        class_body:'body',
        class_head:'head',
        class_close:'close'
    },
    open_preloader:function() {
        var f = function() {
            var l = document.createElement('span');
            l.style.position = 'absolute';
            l.style.top = '0px';
            l.style.left = '0px';
            l.innerHTML = 'loading';
            l.setAttribute('id','preloader');
            document.body.appendChild(l);
            ID_PRELOAD_INTERVAL = setInterval(function() {
                var p =l.innerHTML.substr(8);
                if(p.length >= 4) l.innerHTML = 'loading'; else l.innerHTML += '.';
            },950);
        }
        f();
    },
    complete_preload:function() {
        w.setupAhr({
            //secure:true,
            type:'json',
            error:function(data,st) {
                
            },
            open:function() {
                
                return true;
            },
            complete:function() {
                
            }
        });

        w.setupCas({
            method:'POST',
            error:function(data,st) {
                
            },
            open:function() {
                
            },
            complete:function() {
                
            }
        });
        
        clearInterval(ID_PRELOAD_INTERVAL);
        document.body.removeChild(document.getElementById('preloader'));
    }
}
