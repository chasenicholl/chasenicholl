( function() {
    
    var script_loaded_count = 0;
    
    Comet = {
        
        isReady: function(callback)
        {
            if (typeof callback == 'function') {
                var interval;    
                interval = setInterval( function() {
                    if (script_loaded_count == COMET_SCRIPT_COUNT) {
                        clearInterval(interval);
                        return callback();
                    }
                }, 1);
            }
        },
        
        loaded: function()
        {
            script_loaded_count += 1;
        },
        
        extend: function(obj, override)
        {
            if (override == undefined || typeof override != 'boolean') {
                override = false;
            }
            
            for (func in obj) {
                if (override) {
                    this[func] = obj[func];
                }
                else {
                    if (this[func] == undefined) {
                        this[func] = obj[func];
                    }
                }
            }
        },
        
        elem: function(key)
        {
            
            var elem = this.getElement(key);
            
            return {
                
                on: function(event, callback)
                {
                    
                    switch (event) {
                        
                        case 'click':
                            
                            if (elem) {                                
                                                             
                                if (elem.length > 1) {
                                    
                                    for (var i = 0; i < elem.length; i++) {
                                        elem[i].addEventListener( 'click', function(e) {
                                            callback(e);
                                        } );
                                    }
                                    
                                }
                                else {
                                    
                                    if (elem[0] != undefined) {
                                        elem = elem[0];
                                    }
                                    
                                    elem.addEventListener( 'click', function(e) {
                                        callback(e);
                                    } );
                                
                                }
                                
                            }
                            
                            break;
                        
                    }
                    
                }
                    
            };
            
        },
        
        getElement: function(key)
        {
            
            var elem = null;
            
            if (key.match(/#/g)) { // Is ID
            
                key  = key.replace('#', '');
                elem = document.getElementById(key);

            }
            else if (key.match(/./g)) { // Is Class

                if (document.getElementsByClassName) {
                    
                    key  = key.replace('.', '');
                    elem = document.getElementsByClassName(key);
                    
                    /*var elem = [];
                    for (var i = 0; i < _elem; i++) {
                        elem.push(_elem[i]);
                    }*/
                    
                }
                
            }
            else { // Try as tag
                
                if (document.getElementsByTagName) {
                
                    elem = document.getElementsByTagName(key);
                    
                }
                
            }
            
            if (elem != null && elem != undefined) {
                return elem;
            }
                        
        },
        
        getCookies: function()
        {
            var cookies = [];
            var raw_cookies = document.cookie.split(';');
            
            for (var i = 0; i < raw_cookies.length; i++) {
            
                var _cookies = raw_cookies[i].split('=');
                
                var _tmp = [];
                for (var j = 0; j < _cookies.length; j++) {
                    _tmp.push(decodeURIComponent(_cookies[j].replace(/^\s\s*/, '').replace(/\s\s*$/, '')));
                }
                
                cookies["" + _tmp[0] + ""] = _tmp[1];
                
            }
            
            return cookies;
        },
        
        onMobile: function(callback)
        {
            if ($J(window).width() <= 600) {
                if (typeof callback === 'function') {
                    return callback();
                }
            }
            
            $J(window).on( 'resize', function() {
                if ($J(window).width() <= 600) {
                    if (typeof callback === 'function') {
                        return callback();
                    }
                }
            } );
        },
        
        onTablet: function(callback)
        {
            var width = $J(window).width();
            if (width >= 600 && width <= 768) {
                if (typeof callback === 'function') {
                    return callback();
                }
            }
            
            $J(window).on( 'resize', function() {
                var width = $J(window).width();
                if (width >= 600 && width <= 768) {
                    if (typeof callback === 'function') {
                        return callback();
                    }
                }
            } );
        },
        
        onDesktop: function(callback)
        {
            var width = $J(window).width();
            if (width > 768) {
                if (typeof callback === 'function') {
                    return callback();
                }
            }
            
            $J(window).on( 'resize', function() {
                var width = $J(window).width();
                if (width > 768) {
                    if (typeof callback === 'function') {
                        return callback();
                    }
                }
            } );
        },
        
        isMobile: function()
        {
            
            if (navigator.userAgent.match(/Android/i)
                || navigator.userAgent.match(/webOS/i)
                || navigator.userAgent.match(/iPhone/i)
                || navigator.userAgent.match(/iPad/i)
                || navigator.userAgent.match(/iPod/i)
                || navigator.userAgent.match(/BlackBerry/i)
                || navigator.userAgent.match(/Windows Phone/i)) {
                
                return true;
                   
            }
            
            return false;
                 
        },
        
        _GET: function(key)
        {
            var gets = [];
            var query_string = this.getQueryString();
            if (query_string == undefined || query_string == null) return;
            
            var querys = query_string.split('&');
            
            for (var i = 0; i < querys.length; i++) {
        
                var param = querys[i].split('=');
                gets[param[0]] = param[1];
                
            }
            
            if (gets[key] != undefined) {
                return gets[key];
            }    
            
        },
        
        getBaseUrl: function()
        {
            var url = window.location.href.split('?');
            if (url[0] != undefined) {
                return url[0];
            }
            else {
                return url;
            }
        },
        
        getQueryString: function()
        {
            var url = window.location.href.split('?');
            if (url[1] != undefined) {
                return url[1];   
            }
        }
          
    };
    return Comet;
    
} )();