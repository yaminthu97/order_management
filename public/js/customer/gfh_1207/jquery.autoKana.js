const INTERVAL_TIME = 30;
const HIRAGANA_TO_KATAKANA_OFFSET = 96;
const HIRAGANA_UNICODE_START = 12353; // あ : 標準ひらがなの最初の文字
const HIRAGANA_UNICODE_END = 12435;   // ん : 標準ひらがなの最後の文字
const SMALL_KA_HIRAGANA_UNICODE = 12445; // ゕ : 小さい「か」
const SMALL_KE_HIRAGANA_UNICODE = 12446; // ゖ : 小さい「け」

(function ($) {
    $.fn.autoKana = function (element1, element2, passedOptions) {

        var options = $.extend(
            {
                'katakana': false
            }, passedOptions);

        var kana_extraction_pattern = new RegExp('[^ 　ぁあ-んー]', 'g');
        var kana_compacting_pattern = new RegExp('[ぁぃぅぇぉっゃゅょ]', 'g');
        var elName,
            elKana,
            active = false,
            timer = null,
            flagConvert = true,
            input,
            values,
            ignoreString,
            baseKana;

        elName = $(element1);
        elKana = $(element2);
        active = true;
        _stateClear();

        elName.blur(_eventBlur);
        elName.focus(_eventFocus);
        elName.keydown(_eventKeyDown);

        function start() {
            active = true;
        };

        function stop() {
            active = false;
        };

        function toggle(event) {
            var ev = event || window.event;
            if (event) {
                var el = Event.element(event);
                if (el.checked) {
                    active = true;
                } else {
                    active = false;
                }
            } else {
                active = !active;
            }
        };

        function _checkConvert(new_values) {
            if (!flagConvert) {
                if (Math.abs(values.length - new_values.length) > 1) {
                    var tmp_values = new_values.join('').replace(kana_compacting_pattern, '').split('');
                    if (Math.abs(values.length - tmp_values.length) > 1) {
                        _stateConvert();
                    }
                } else {
                    if (values.length == input.length && values.join('') != input) {
                        if (input.match(kana_extraction_pattern)) {
                            _stateConvert();
                        }
                    }
                }
            }
        };

        function _checkValue() {
            var new_input, new_values;
            new_input = elName.val()
            if (new_input == '') {
                _stateClear();
                _setKana();
            } else {
                new_input = _removeString(new_input);
                if (input == new_input) {
                    return;
                }
                input = new_input;
                if (!flagConvert) {
                    new_values = new_input.replace(kana_extraction_pattern, '').split('');
                    _checkConvert(new_values);
                    _setKana(new_values);
                }
            }
        };

        function _clearInterval() {
            clearInterval(timer);
        };

        function _eventBlur(event) {
            _clearInterval();
        };
        function _eventFocus(event) {
            _stateInput();
            _setInterval();
        };
        function _eventKeyDown(event) {
            if (flagConvert) {
                _stateInput();
            }
        };
        function _isHiragana(chara) {
            return ((chara >= HIRAGANA_UNICODE_START && chara <= HIRAGANA_UNICODE_END) || chara == SMALL_KA_HIRAGANA_UNICODE || chara == SMALL_KE_HIRAGANA_UNICODE);
        };
        function _removeString(new_input) {
            if (new_input.indexOf(ignoreString) !== -1) {
                return new_input.replace(ignoreString, '');
            }
            var i, ignoreArray, inputArray;
            ignoreArray = ignoreString.split('');
            inputArray = new_input.split('');
            for (i = 0; i < ignoreArray.length; i++) {
                if (ignoreArray[i] == inputArray[i]) {
                    inputArray[i] = '';
                }
            }
            return inputArray.join('');
        };
        function _setInterval() {
            var self = this;
            timer = setInterval(_checkValue, INTERVAL_TIME);
        };
        function _setKana(new_values) {
            if (!flagConvert) {
                if (new_values) {
                    values = new_values;
                }
                if (active) {
                    var _val = _toKatakana(baseKana + values.join(''));
                    elKana.val(_val);
                }
            }
        };
        function _stateClear() {
            baseKana = '';
            flagConvert = false;
            ignoreString = '';
            input = '';
            values = [];
        };
        function _stateInput() {
            baseKana = elKana.val();
            flagConvert = false;
            ignoreString = elName.val();
        };
        function _stateConvert() {
            baseKana = baseKana + values.join('');
            flagConvert = true;
            values = [];
        };
        function _toKatakana(src) {
            if (options.katakana) {
                var c, i, str;
                str = new String;
                for (i = 0; i < src.length; i++) {
                    c = src.charCodeAt(i);
                    if (_isHiragana(c)) {
                        str += String.fromCharCode(c + HIRAGANA_TO_KATAKANA_OFFSET);
                    } else {
                        str += src.charAt(i);
                    }
                }
                return str;
            } else {
                return src;
            }
        }
    };
})(jQuery);
