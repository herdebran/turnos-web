
$().ready(function () {

// Reloj de tiempo de session
    var $reloj = $("#relojito");
    var extra = $reloj.data("time");
    var tiempoExtraCalculado = (extra / 1000);

    function calcTime(mas) {
        if (mas === undefined) {
            mas = 0;
        }
        return moment($.now()).add(tiempoExtraCalculado + mas, 's').format('YYYY/MM/DD HH:mm:ss');
    }

    function get_hostname(url) {
        var pattern = /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
        var m = url.match(pattern);
        return m ? m[0] : null;
    }

    $reloj.countdown(calcTime(1), function (event) {
        $(this).text(event.strftime('%H:%M:%S'));
    }).on('finish.countdown', function () {
        location.reload();
    });
});

$(document).ajaxSend(function (event, xhr, options) {

// Reloj de tiempo de session
    var $reloj = $("#relojito");
    var extra = $reloj.data("time");
    var tiempoExtraCalculado = (extra / 1000);

    function calcTime(mas) {
        if (mas === undefined) {
            mas = 0;
        }
        return moment($.now()).add(tiempoExtraCalculado + mas, 's').format('YYYY/MM/DD HH:mm:ss');
    }

    function get_hostname(url) {
        var pattern = /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
        var m = url.match(pattern);
        return m ? m[0] : null;
    }
    if (get_hostname(options.url) == null) {
        $reloj.countdown(calcTime(1))
    }
});