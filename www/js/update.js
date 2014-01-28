(function($){
    window.Prices = {
        currency: 'BTC',
        interval: null,
        title: '',
        update: function(data) {
            document.title = data.vwap + " " + data.currency_code + " | " + this.title;

            $('#curr_price').text(data.vwap);
            $('#curr_date').text(data.date);
            $('.curr_currency').html('<acronym title="' + data.currency_name + '">' + data.currency_code + '</acronym>');

            if (data.btc_vwap) {
                $('#doge_price').text(data.doge_vwap);
                $('#btc_price').text(data.btc_vwap);
            }
            $('table.volumes tbody tr').remove();

            for (var i in data.markets) {
                var row = data.markets[i];
                var tr = $('<tr></tr>');
                tr.append($('<td><a href="' + row.market_url + '">' + row.exchange_name + '</a><br>(' + Math.floor((data.ts-row.ts)/60) + 'm ago)</td>'));
                tr.append($('<td>' + row.price + '</td>'));
                tr.append($('<td>' + row.volume + '</td>'));
                $('table.volumes tbody').append(tr);
            }

            $('table.volumes tbody a').on('click', function() {
                window.open(this.getAttribute('href'));
                return false;
            });

            data.chart = [];
            for (var i in data.history) {
                data.chart[i] = [data.history[i].ts * 1000, data.history[i].vwap];
            }

            data.vchart = [];
            for (var i in data.markets) {
                data.vchart[i] = {label: data.markets[i].exchange_name, data: data.markets[i].volume};
            }

            $.plot($('.value_24h'), [data.chart], {
                xaxis: {mode: 'time'}
            });
            $.plot($('.volume_chart'), data.vchart, {
                series: {
                    pie: {
                        show: true,
                        radius: 1,
                        label: {
                            show: true,
                            radius: 0.6,
                            formatter: function(label, series) {
                                return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+(Math.round(series.percent * 10)/10)+'%</div>';
                            },
                            background: { opacity:0.5 }
                        }
                    }
                },
                legend: {
                    show: false
                }
            });
        },
        init: function() {
            var data = $.parseJSON($('.initialdata').text());
            var self = this;

            this.title = document.title;
            this.currency = data.currency_code;
            this.update(data);
            
            this.interval = setInterval(function() {
                $.get('/' + self.currency + '.json').done(function(data) {
                    self.update(data);
                });
            }, 60000);
        }
    };

    $(document).ready(function(){
        Prices.init();

        window._gaq = [];
        _gaq.push(['_setAccount', 'UA-38513874-2']);
        _gaq.push(['_trackPageview']);
        
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    });
})(jQuery);
