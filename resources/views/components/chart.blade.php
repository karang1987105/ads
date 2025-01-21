@if(empty($update))
    <script type="application/javascript" id="script_{{$id}}">
        (function () {
            let el = $('#{{$id}}');
            if (el.length === 0) { // First loading
                $('<div id="{{$id}}" class="{{ $classes ?? 'col' }} pt-3 mb-2"  style="background: linear-gradient(180deg, cornflowerblue, white, white);"><canvas></canvas></div>').insertAfter($('#script_{{$id}}'));
                el = $('#{{$id}}');
                el[0].chart = new Chart(el.find('canvas'), {
                    type: "{{$type}}",
                    data: {!! $data ?? '{datasets:'.$datasets.'}' !!},
                    options: {!! $options !!}
                });
            } else { // Refresh button
                el[0].chart.data = {!! $data ?? '{datasets:'.$datasets.'}' !!};
                el[0].chart.update();
            }
        })();
    </script>
@else
    <script type="application/javascript">
        (function () {
            const chart = document.querySelector('#{{$id}}').chart;
            chart.data = {!! $data ?? '{datasets:'.$datasets.'}' !!}
            chart.update(); // Search reloading
        })();
    </script>
@endif