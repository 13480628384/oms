<{script src="coms/pager.js"  app='desktop'}>
<{script src="coms/modedialog.js" app="desktop"}>
<{script src="coms/autocompleter.js" app="desktop"}>

<script>
(function(){
<{if $newLogo}>
    $$('#header .logo a')[0].set('html', '<{$newLogo}>');
<{/if}>
<{if $env.conf.desktop.sale_id == 'shopex.erp'}>
<{if $verLogo}>
var e2 = new Element('span');
e2.set('title', "<{$logoMesaage.ver}>");
e2.set('html', "&nbsp;&nbsp;<img src=\"<{$verLogo}>\" />");
e2.inject($$('#header .head-license')[0]);
<{/if}>
<{if $warningLogo}>
var e3 = new Element('span');
e3.set('title', "<{$logoMesaage.warning}>");
e3.set('html', "&nbsp;&nbsp;<img src=\"<{$warningLogo}>\" />");
e3.inject($$('#header .head-license')[0]);
<{/if}>
var el = new Element('span');
var qq_url = "http://b.qq.com/webc.htm?new=0&sid=800103227&o=www.shopex.cn&q=7&ref=+";
var header_qq='&nbsp&nbsp&nbsp&nbsp&nbsp<img  style="CURSOR: pointer" onclick="javascript:window.open(\'http://b.qq.com/webc.htm?new=0&sid=800103227&o=www.shopex.cn&q=7&ref=\'+document.location,\'_blank\', \'height=544, width=644,toolbar=no,scrollbars=no,menubar=no,status=no\')"  border="0" SRC="http://im.bizapp.qq.com:8000/zx_qq.gif">';

el.set('html', header_qq);el.inject($$('#header .head-license')[0]);
<{/if}>
})();

    window.addEvent('domready', setTimeout(function(){

            new Request({url:'index.php?app=ome&ctl=admin_service_taobao&act=validity',method:'get',
                onComplete:function(json){
                    if(!json) {
                        return;
                    }
                   
                    json = JSON.decode(json);
                    has_expire = json.has_expire;
                    if(has_expire) {
                        new Dialog('index.php?app=ome&ctl=admin_service_taobao&act=alert', {
                            width: 800,
                            height: 200,
                            modal: true,
                            title: '淘宝SESSION过期提醒'
                        });
                    }
                }
            }).send();


    }, 6000));
    window.addEvent('domready', setTimeout(function(){
        if ('<{$session_warning}>' == 'false') {
            alert("<{$logoMesaage.warning_alert}>");
        }
    }, 7000));
</script>
