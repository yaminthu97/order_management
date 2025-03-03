<style type="text/css">
    table.select-item {
        float: left;
    }

    button.toggle-btn {
        height: 32px;
    }
</style>
<table class="c-tbl c-tbl--full c-tbl-border-all calendar-bulk u-mt--ss select-item">
    <tr>
        <th class="c-box--100">年度</th>
        <td class="c-box--200 u-center">
            @if (empty($notify))
                <select class="form-control c-box--100" id="calendar_year" name="year">
                    @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                        <option value="{{ $y }}" @if ($y == $year) selected @endif>
                            {{ $y }}年</option>
                    @endfor
                </select>
            @else
                {{ $year }}年
            @endif
        </td>
        @if (empty($notify))
            <th class="c-box--150">休日設定区分</th>
            <td class="c-box--200 u-center">
                <select class="form-control c-box--100" id="calender_ho_type">
                    <option value="1">月</option>
                    <option value="2">火</option>
                    <option value="3">水</option>
                    <option value="4">木</option>
                    <option value="5">金</option>
                    <option value="6" selected>土</option>
                    <option value="0">日</option>
                </select>
            </td>
            <td class="u-center">
                <button class="btn btn-primary toggle-btn ac-en u-mr--sl" type="button">一括設定</button>
                <button class="btn btn-primary toggle-btn ac-di" type="button">一括解除</button>
            </td>
        @endif
    </tr>
</table>
<div class="c-box--full u-mt--xs">
    <div class="calendar">
        <table class="c-tbl--full table-link">
            <tr>
                <td></td>
                @for ($i = 1; $i <= 6; $i++)
                    <td class="holiday">日</td>
                    <td>月</td>
                    <td>火</td>
                    <td>水</td>
                    <td>木</td>
                    <td>金</td>
                    <td class="holiday">土</td>
                @endfor
            </tr>
            @foreach ($calendar_arr as $month => $days)
                <tr>
                    <th>{{ $month }}月</th>
                    @foreach ($days as $index => $day)
                        @php
                            $datestr = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day);
                            $class = '';
                            if (($index ** 2 + $index) % 7 == 0) {
                                $class = 'holiday';
                            }
                            if (!empty($choices[$datestr])) {
                                $class = 'choice';
                            }
                            $wkDate = Date('w', strtotime($datestr));
                        @endphp
                        <td data-datestr="{{ $datestr }}" data-wk="{{ $wkDate }}" class="{{ !empty($day) ? "holiday_select" : "" }} {{ $class }}">{{ $day }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
        <script>
            @if (empty($notify))
                make_input_holidays();
                $(".holiday_select").click(function() {
                    $(this).toggleClass("choice");
                    $(".input_holidays").remove();
                    make_input_holidays();
                });

                function make_input_holidays() {
                    $('input[name="holidays[]"]').remove();
                    $(".choice").each(function() {
                        $("<input>").attr({
                            type: "hidden",
                            name: "holidays[]",
                            class: "input_holidays",
                            value: $(this).data("datestr")
                        }).appendTo("form");
                    });
                }

                function calendar_refresh(year) {
                    $.ajaxSetup({});
                    $.ajax({
                        type: "POST",
                        url: "/gfh/master/warehouses/" + {{ $m_warehouses_id }} + "/getCalendar/" + year +
                            "/{{ !empty($notify) }}",
                        dataType: "html",
                        headers: {
                            'X-XSRF-TOKEN': decodeURIComponent(getCookieValue("XSRF-TOKEN"))
                        },
                        success: function(data) {
                            $("#tabs-2").html(data);
                        }
                    });
                }

                $("#calendar_year").change(function() {
                    calendar_refresh($("#calendar_year").val());
                });
            @endif
            function getCookieValue(targetKey) {
                for (let cookie of document.cookie.split(";")) {
                    const [key, value] = cookie.trim().split("=");
                    if (key === targetKey) {
                        return value;
                    }
                }
                return "";
            }
        </script>
    </div><!-- calendar -->
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('button.toggle-btn.ac-en').on('click', function() {
            var val = $('#calender_ho_type').val();
            if (val == 60) {
                var target = $('[data-wk="6"]');
                for (var i = 0; i < target.length; i++) {
                    if ($(target[i]).text() != "") {
                        $(target[i]).addClass('choice');
                    }
                }
                var target = $('[data-wk="0"]');
                for (var i = 0; i < target.length; i++) {
                    if ($(target[i]).text() != "") {
                        $(target[i]).addClass('choice');
                    }
                }
            } else {
                var target = $('[data-wk="' + val + '"]');
                for (var i = 0; i < target.length; i++) {
                    if ($(target[i]).text() != "") {
                        $(target[i]).addClass('choice');
                    }
                }
            }
            make_input_holidays();
        });

        $('button.toggle-btn.ac-di').on('click', function() {
            var val = $('#calender_ho_type').val();
            if (val == 60) {
                var target = $('[data-wk="6"]');
                for (var i = 0; i < target.length; i++) {
                    if ($(target[i]).text() != "") {
                        $(target[i]).removeClass('choice');
                    }
                }
                var target = $('[data-wk="0"]');
                for (var i = 0; i < target.length; i++) {
                    if ($(target[i]).text() != "") {
                        $(target[i]).removeClass('choice');
                    }
                }
            } else {
                var target = $('[data-wk="' + val + '"]');
                for (var i = 0; i < target.length; i++) {
                    if ($(target[i]).text() != "") {
                        $(target[i]).removeClass('choice');
                    }
                }
            }
            make_input_holidays();
        });
    });
</script>
