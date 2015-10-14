function HurDur(opts) {
    var hurrr = 1000;
    var hurrrrr;
    var cats = 1;

    if (opts && opts.speed) hurrr = opts.speed;
    if (opts && opts.loopcb) hurrrrr = opts.loopcb;
    if (opts && opts.cats) cats = opts.cats;

    var a = '0123456789abcdef';
    var hurrdur;

    var hurr = $('#menubox');
    var durr = $('body');
    var hurr_c = hurr.css('background-color');
    var durr_c = durr.css('background-color');
    var run = false;
    var cnt = 0;

    this.start = function () {
        run = true;
        hurrdurr();
        hurrdur = setInterval(hurrdurr, 1000);
    };

    this.stop = function () {
        run = false;
        clearInterval(hurrdur);
        setTimeout(function() {
            hurr.css('background-color', hurr_c);
            durr.css('background-color', durr_c);
            cnt = 0;
        }, hurrr/3+10);

    };

    function randstr() {
        var str = '';
        for (var i = 0; i < 6; ++i)
            str += a[Math.floor(Math.random() * 16)];
        return str;
    }

    function hurrdurr() {
        cnt++;
        if(cnt%cats === 0) spawnCat();
        if (hurrrrr) hurrrrr();
        hurrrdurrr(hurr);
        hurrrdurrr(durr);
    }

    function hurrrdurrr(elem) {
        var hu = hurrr/3;
        if(run) elem.stop().animate({backgroundColor: '#' + randstr()}, hu,
            function () {
                if(run) elem.stop().animate({backgroundColor: '#' + randstr()}, hu,
                    function () {
                        if(run) elem.stop().animate({backgroundColor: '#' + randstr()}, hu);
                    });
            });
    }

    function spawnCat() {
        var cat = $('#nyan').clone();
        $('#nyan').after(cat);
        cat.css('top', ((cnt*33)%80)+'%').css('left','-100');
        cat.animate({left:'+=2000'}, 2000, 'swing', function() {
            cat.remove();
        });
    }
}