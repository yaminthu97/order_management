<?php
namespace App\Validator;

use Illuminate\Validation\Validator;

class CustomValidator extends Validator
{
    /**
     * 電話番号かどうか
     */
    public function validateTel($attribute, $value, $parameters)
    {
        // 空欄の場合はチェックしない
        if(strlen($value) == 0 || $value == null) {
            return true;
        }

        return preg_match('/^\+?\d{0,5}-?\d{1,5}-?\d{1,5}-?\d{1,5}$/', $value);
    }

    /**
     * メールアドレス(RFCより緩めに)
     */
    public function validateEmailNotrfc($attribute, $value, $parameters)
    {
        return preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", $value);
    }

    /**
     * 郵便番号かどうか
     */
    public function validatePostal($attribute, $value, $parameters)
    {
        // 空欄の場合はチェックしない
        if(strlen($value) == 0 || $value == null) {
            return true;
        }

        $isValid = preg_match('/^\d{3}-\d{4}$/', $value);

        if(!$isValid) {
            $isValid = preg_match('/^\d{7}$/', $value);
        }

        return $isValid;
    }

    /**
     * 郵便番号かどうか
     * (ハイフンを許容しない)
     */
    public function validatePostal7only($attribute, $value, $parameters)
    {
        // 空欄の場合はチェックしない
        if(strlen($value) == 0 || $value == null) {
            return true;
        }

        $isValid = preg_match('/^\d{7}$/', $value);

        return $isValid;
    }

    /**
     * SJISの機種依存文字を含んでいるかどうか
     */
    public function validateSjisword($attribute, $value, $parameters)
    {
        // 空欄の場合はチェックしない
        if(strlen($value) == 0 || $value == null) {
            return true;
        }

        $valueStrLen = strlen($value);

        $convStrLen = strlen(mb_convert_encoding(mb_convert_encoding($value, 'SJIS-win', 'UTF-8'), 'UTF-8', 'SJIS-win'));

        return $valueStrLen === $convStrLen;
    }

    /**
     * 文字列バイト数が超過していないかどうか
     */
    public function validateStrLengthMaxByte($attribute, $value, $parameters)
    {
        $maxLength = $parameters[0];

        if(strlen($value) <= $maxLength) {
            return true;
        }

        return false;
    }

    /**
     * 文字列バイト数が超過していないかどうか
     * (メッセージ返却)
     */
    public function replaceStrLengthMaxByte($message, $attribute, $rule, $parameters)
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * 文字列バイト数が範囲内にあるかどうか
     */
    public function validateStrLengthBetweenByte($attribute, $value, $parameters)
    {
        $minLength = $parameters[0];
        $maxLength = $parameters[1];

        if((strlen($value) >= $minLength) && (strlen($value) <= $maxLength)) {
            return true;
        }

        return false;
    }

    /**
     * 文字列バイト数が範囲内にあるかどうか
     * (メッセージ返却)
     */
    public function replaceStrLengthBetweenByte($message, $attribute, $rule, $parameters)
    {
        return str_replace(':min', $parameters[0], str_replace(':max', $parameters[1], $message));
    }

    /**
     * 文字列文字数が超過していないかどうか
     */
    public function validateStrLengthMaxCount($attribute, $value, $parameters)
    {
        $maxLength = $parameters[0];

        if(mb_strlen($value) <= $maxLength) {
            return true;
        }

        return false;
    }

    /**
     * 文字列文字数が超過していないかどうか
     * (メッセージ返却)
     */
    public function replaceStrLengthMaxCount($message, $attribute, $rule, $parameters)
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * 文字列文字数が範囲内にあるかどうか
     */
    public function validateStrLengthBetweenCount($attribute, $value, $parameters)
    {
        $minLength = $parameters[0];
        $maxLength = $parameters[1];

        if((mb_strlen($value) >= $minLength) && (mb_strlen($value) <= $maxLength)) {
            return true;
        }

        return false;
    }

    /**
     * 文字列文字数が範囲内にあるかどうか
     * (メッセージ返却)
     */
    public function replaceStrLengthBetweenCount($message, $attribute, $rule, $parameters)
    {
        return str_replace(':min', $parameters[0], str_replace(':max', $parameters[1], $message));
    }

    /**
     * 数値が指定値以下かどうか
     */
    public function validateNumMax($attribute, $value, $parameters)
    {
        $max = $parameters[0];

        return $value <= $max;
    }

    /**
     * 数値が指定値以下かどうか
     * (メッセージ返却)
     */
    public function replaceNumMax($message, $attribute, $rule, $parameters)
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * 数値が指定値以上かどうか
     */
    public function validateNumMin($attribute, $value, $parameters)
    {
        $min = $parameters[0];

        return $value >= $min;
    }

    /**
     * 数値が指定値以上かどうか
     * (メッセージ返却)
     */
    public function replaceNumMin($message, $attribute, $rule, $parameters)
    {
        return str_replace(':min', $parameters[0], $message);
    }

    /**
     * 数値が範囲内にあるかどうか
     */
    public function validateNumBetween($attribute, $value, $parameters)
    {
        $min = $parameters[0];
        $max = $parameters[1];

        if($min <= $value && $max >= $value) {
            return true;
        }

        return false;
    }

    /**
     * 数値が範囲内にあるかどうか
     * (メッセージ返却)
     */
    public function replaceNumBetween($message, $attribute, $rule, $parameters)
    {
        return str_replace(':min', $parameters[0], str_replace(':max', $parameters[1], $message));
    }

}
