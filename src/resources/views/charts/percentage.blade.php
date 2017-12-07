<?php $id = str_random(10); $history = $history->withMeasurements(); ?>
<script type="text/javascript">
    google.charts.load("current", {packages: ["corechart"]});
    google.charts.setOnLoadCallback(function() {
        var container = document.getElementById('chart-{{$id}}');
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Time', '% {{$history->last()['measurement']->label()}}', '% Threshold'],
            @foreach($history as $check)
            ['{{date('Y-m-d H:i', $check['time'])}}', {{$check['measurement']->number()}}, {{$check['measurement']->threshold()}}],
            @endforeach
        ]);

        var options = {
            vAxis: {title: '%', minValue: 0},
            hAxis: {textPosition: 'none'},
            seriesType: 'area',
            series: {0: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(container);
        chart.draw(data, options);
    });

</script>

<div id="chart-{{$id}}" style="height: 400px;"></div>
