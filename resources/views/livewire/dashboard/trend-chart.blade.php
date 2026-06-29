<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-5"
     wire:ignore.self>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-semibold" style="color:#1e3a5f">Riwayat Realisasi Bantuan Sosial</h3>
            <p class="text-xs mt-0.5" style="color:#9ca3af">Perbandingan antara realisasi dan target pada periode penyaluran sepanjang tahun</p>
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs" style="color:#6b7280">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-5 h-0" style="border-top:2px dashed #94a3b8"></span>Target
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-5 h-0.5 rounded" style="background:#1e3a5f"></span>Realisasi PKH
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-5 h-0.5 rounded" style="background:#8b1a2f"></span>Realisasi Sembako
            </span>
        </div>
    </div>

    <div wire:ignore>
        <div id="trend-chart-el" style="height:260px;width:100%"></div>
    </div>
</div>

<script>
(function () {
    var chart = null;
    var lastDataJson = '';
    var resizeTimeout = null;

    var colors = {
        PKH:              { target: '#94a3b8', realisasi: '#1e3a5f' },
        SEMBAKO:          { target: '#f9a8b4', realisasi: '#8b1a2f' },
        STIMULUS_SEMBAKO: { target: '#fca5a5', realisasi: '#c53030' },
        BLT_KESRA:        { target: '#93c5fd', realisasi: '#2563eb' },
    };

    function getResponsiveSettings() {
        var w = window.innerWidth;
        if (w < 480) {
            return { height: 240, fontSize: 9, symbolSize: 3, gridLeft: 8, gridRight: 8, gridBottom: 64, rotate: 55 };
        } else if (w < 768) {
            return { height: 260, fontSize: 10, symbolSize: 4, gridLeft: 12, gridRight: 12, gridBottom: 56, rotate: 40 };
        } else {
            return { height: 300, fontSize: 11, symbolSize: 5, gridLeft: 16, gridRight: 16, gridBottom: 24, rotate: 0 };
        }
    }

    function renderChart(data) {
        if (!data || !data.series || !data.series.length) return;

        var el = document.getElementById('trend-chart-el');
        if (!el) return;

        var rs = getResponsiveSettings();
        el.style.height = rs.height + 'px';

        if (!chart) {
            chart = echarts.init(el);
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function () {
                    if (!chart) return;
                    var newRs = getResponsiveSettings();
                    el.style.height = newRs.height + 'px';
                    chart.resize();
                    if (lastDataJson) renderChart(JSON.parse(lastDataJson));
                }, 150);
            });
        }

        var series = [];
        data.series.forEach(function(s) {
            var c = colors[s.kode] || { target: '#d1d5db', realisasi: '#6b7280' };
            series.push({
                name: 'Target ' + s.nama,
                type: 'line',
                data: s.target,
                smooth: true,
                lineStyle: { type: 'dashed', color: c.target, width: 1.5 },
                itemStyle: { color: c.target },
                symbol: 'none',
                z: 2,
            });
            series.push({
                name: 'Realisasi ' + s.nama,
                type: 'line',
                data: s.realisasi.map(function(val, index) {
                    return {
                        value: val,
                        persentase: s.pct_kpm ? (s.pct_kpm[index] ?? 0) : 0
                    };
                }),
                smooth: true,
                lineStyle: { color: c.realisasi, width: 2.5 },
                itemStyle: { color: c.realisasi },
                symbol: 'circle',
                symbolSize: rs.symbolSize,
                z: 3,
                // --- Tambahan: efek gradasi di bawah garis ---
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: hexToRgba(c.realisasi, 0.28) },
                        { offset: 1, color: hexToRgba(c.realisasi, 0.02) }
                    ])
                },
            });
        });

        chart.setOption({
            tooltip: {
                trigger: 'axis',
                backgroundColor: '#fff',
                borderColor: '#e5e7eb',
                borderWidth: 1,
                padding: [10, 14],
                confine: true,
                extraCssText: 'max-width: 80vw; word-wrap: break-word;',
                textStyle: { fontSize: 12, color: '#374151' },

                formatter: function (params) {
                    if (!params || params.length === 0) return '';

                    var res = '<div style="font-weight:700; margin-bottom:8px; color:#1e3a5f; font-size:13px; border-bottom:2px solid #1e3a5f; padding-bottom:6px">'
                        + params[0].axisValue + '</div>';

                    var grouped = {};
                    var order = [];
                    params.forEach(function(item) {
                        var isTarget = item.seriesName.startsWith('Target ');
                        var nama = isTarget
                            ? item.seriesName.replace('Target ', '')
                            : item.seriesName.replace('Realisasi ', '');

                        if (!grouped[nama]) {
                            grouped[nama] = {};
                            order.push(nama);
                        }
                        if (isTarget) {
                            grouped[nama].target = item;
                        } else {
                            grouped[nama].realisasi = item;
                        }
                    });

                    order.forEach(function(nama) {
                        var g = grouped[nama];
                        res += '<div style="margin-top:8px">';

                        res += '<div style="font-weight:700; color:#1e3a5f; font-size:12px; margin-bottom:4px">'
                            + nama + '</div>';

                        if (g.target) {
                            var targetVal = (g.target.value ?? 0).toLocaleString('id-ID');
                            res += '<div style="padding-left:12px; margin-bottom:2px; color:#6b7280; font-size:11px">'
                                + g.target.marker
                                + ' Target: <span style="font-weight:600; color:#374151">' + targetVal + ' KPM</span>'
                                + '</div>';
                        }

                        if (g.realisasi) {
                            var realisasiVal = (g.realisasi.value ?? 0).toLocaleString('id-ID');
                            var pct = 0;
                            if (g.realisasi.data && g.realisasi.data.persentase !== undefined) {
                                pct = parseFloat(g.realisasi.data.persentase).toFixed(1);
                            } else if (g.target && g.target.value) {
                                pct = ((g.realisasi.value / g.target.value) * 100).toFixed(1);
                            }
                            var pctColor = pct >= 90 ? '#16a34a' : pct >= 70 ? '#ca8a04' : '#8b1a2f';

                            res += '<div style="padding-left:12px; color:#6b7280; font-size:11px">'
                                + g.realisasi.marker
                                + ' Realisasi: <span style="font-weight:600; color:#374151">' + realisasiVal + ' KPM</span>'
                                + ' <span style="font-weight:700; color:' + pctColor + '">(' + pct + '%)</span>'
                                + '</div>';
                        }

                        res += '</div>';
                    });

                    return '<div style="font-size:12px; min-width:180px; max-width:260px; padding:2px">' + res + '</div>';
                }
            },
            grid: {
                left: rs.gridLeft,
                right: rs.gridRight,
                bottom: rs.gridBottom,
                top: 12,
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: data.labels,
                axisLabel: {
                    fontSize: rs.fontSize,
                    color: '#9ca3af',
                    rotate: rs.rotate,
                    interval: 0,        
                    hideOverlap: false  
                },
                axisLine: { lineStyle: { color: '#e5e7eb' } },
                axisTick: { show: false },
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    fontSize: rs.fontSize,
                    color: '#9ca3af',
                    formatter: function(v) {
                        return v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : v.toLocaleString('id-ID');
                    }
                },
                splitLine: { lineStyle: { color: '#f3f4f6' } },
            },
            series: series,
        }, true);
    }

    function hexToRgba(hex, alpha) {
        var h = hex.replace('#', '');
        if (h.length === 3) {
            h = h.split('').map(function(c) { return c + c; }).join('');
        }
        var r = parseInt(h.substring(0, 2), 16);
        var g = parseInt(h.substring(2, 4), 16);
        var b = parseInt(h.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function startPolling() {
        setInterval(function() {
            var components = Livewire.all();
            var trendComponent = null;

            components.forEach(function(c) {
                if (c.name === 'dashboard.trend-chart') {
                    trendComponent = c;
                }
            });

            if (!trendComponent) return;

            var snapshot = trendComponent.snapshot;
            if (!snapshot || !snapshot.data) return;
            var rawChartData = snapshot.data.chartData;
            if (!rawChartData || !rawChartData[0]) return;

            var raw = rawChartData[0];
            var data = {
                labels: raw.labels[0],
                series: raw.series[0].map(function(s) {
                    return {
                        nama: s[0].nama,
                        kode: s[0].kode,
                        target: s[0].target[0],
                        realisasi: s[0].realisasi[0],
                        pct_kpm: s[0].pct_kpm ? s[0].pct_kpm[0] : []
                    };
                })
            };
            if (!data) return;

            var dataJson = JSON.stringify(data);
            if (dataJson === lastDataJson) return;

            lastDataJson = dataJson;
            renderChart(data);
        }, 800);
    }

    function init() {
        if (typeof echarts === 'undefined' || typeof Livewire === 'undefined') {
            setTimeout(init, 200);
            return;
        }
        startPolling();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>