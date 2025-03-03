<?php

namespace App\Modules\Order\Gfh1207;

use App\Enums\ItemNameType;
use App\Http\Requests\Gfh1207\ImportEcbeingCustDataRequest;
use App\Models\Cc\Gfh1207\CustModel;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Modules\Order\Base\ImportEcbeingCustDataInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Worksheet\Validations;

/**
 *  Ecbeing顧客取込データを更新または作成する機能
 */
class ImportEcbeingCustData implements ImportEcbeingCustDataInterface
{
    //get text file path to save on S3 server
    protected $getTextExportFilePath;

    //tsv format check
    protected $tsvFormatCheck;

    //for s3
    protected $s3;

    //guest customer constants
    private const GUEST_CUSTOMER = "-1";

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    //ecbing customer column count constants
    private const  ECBEING_CUST_COLUMN_COUNT = 38;

    public function __construct(
        GetTextExportFilePath $getTextExportFilePath,
        TsvFormatCheck $tsvFormatCheck,
    ) {
        $this->getTextExportFilePath = $getTextExportFilePath;
        $this->tsvFormatCheck = $tsvFormatCheck;
        $this->s3 = config('filesystems.default', 'local');
    }

    /**
     * Ecbeing顧客取込データを更新または作成する
     * @param string (customer file path)
     * @param int (account id)
     * @param string (account code)
     * @param string (batch type)
     * @param int (batch id)
     * @param int (operators Id)
     * @return int (total rows count)
     */
    public function execute($customerTsvFilePath, $accountId, $accountCode, $batchType, $bathID, $operatorsId)
    {
        //get tsv file contents
        $fileContents = Storage::disk($this->s3)->get($customerTsvFilePath);

        // 文字コードをチェックし、UTF-8に変換する
        $encoding = mb_detect_encoding($fileContents, ['SJIS-win', 'SJIS-WIN', 'SJIS', 'UTF-8', 'EUC-JP'], true);
        if ($encoding) {
            // SJIS系の表記揺れを統一（SJIS-win / SJIS-WIN → SJIS）
            if (stripos($encoding, 'SJIS') !== false) {
                $encoding = 'SJIS'; // 統一する
            }
            
            // UTF-8 に変換
            $fileContents = mb_convert_encoding($fileContents, 'UTF-8', $encoding);
        }

        // file data is tsv format or not
        $tsvFormatCheck = $this->tsvFormatCheck->execute($fileContents);
        if (!$tsvFormatCheck) {
            // 取込ファイル内容フォーマットが間違っています。
            throw new Exception(__('messages.error.input_file_format_error'), self::PRIVATE_THROW_ERR_CODE);
        }

        // Convert TSV to Array
        $customerArrayData = $this->convertTsvToArray($fileContents, $accountId);

        // data validation
        $this->validateCustomerData($customerArrayData, $accountCode, $batchType, $bathID);

        // Get total number of rows
        $totalRowCnt = count($customerArrayData);

        //Web会員番号がゲスト会員（-1）の場合
        $guestCustomers = array_filter($customerArrayData, fn($customer) => $customer['reserve10'] == self::GUEST_CUSTOMER);

        //ゲスト会員以外の場合
        $customers = array_filter($customerArrayData, fn($customer) => $customer['reserve10'] != self::GUEST_CUSTOMER);

        try {

            //ゲスト顧客が存在する場合
            if (!empty($guestCustomers)) {
                //ゲスト顧客を更新または作成する
                foreach ($guestCustomers as $guestCustomer) {
                    $custRecord = CustModel::updateOrCreate(
                        [
                            'tel1' => $guestCustomer['tel1'],
                            'name_kanji' => $guestCustomer['name_kanji'],
                            'address1' => $guestCustomer['address1'],
                            'address2' => $guestCustomer['address2'],
                            'address3' => $guestCustomer['address3'],
                            'address4' => $guestCustomer['address4'],
                        ],
                        $guestCustomer
                    );
                    $this->applyCustomerRecordUpdates($custRecord, $accountId, $operatorsId);
                }
            }

            //ゲスト以外の顧客が存在する場合
            if (!empty($customers)) {
                //ゲスト以外の顧客を更新または作成する
                foreach ($customers as $customer) {
                    $custRecord = CustModel::updateOrCreate(
                        ['reserve10' => $customer['reserve10']],
                        $customer
                    );
                    $this->applyCustomerRecordUpdates($custRecord, $accountId, $operatorsId);
                }
            }

            return $totalRowCnt;
        } catch (Exception $e) {
            // 顧客・受注データ登録時のエラー場合
            // 顧客データ登録・更新処理で異常が発生しました。
            throw new Exception(__('messages.error.process_something_wrong', ['process' => '顧客データ登録・更新処理']), self::PRIVATE_THROW_ERR_CODE);
        }
    }


    /**
     * Ecbeing から ESM への TSV データを配列データに準備する
     * @param string (tsv raw data)
     * @param int (account id)
     * @return array (convert tsv to array)
     */

    private function convertTsvToArray($tsvData, $accountId)
    {
        // Split by lines
        $lines = explode("\n", $tsvData);
        // Remove empty elements
        $lines = array_filter($lines);
        // Re-index the array
        $lines = array_values($lines);

        // Define the mapping of indices to ESM keys（index = tst file column index, key = field name of m_cust table）
        $keyMapping = [
            0 => 'reserve10', //自由項目１０ required
            1 => 'corporate_kanji', //法人名・団体名
            2 => 'division_name', //部署名
            3 => 'name_kanji1', //氏名漢字 required
            4 => 'name_kanji2', //氏名漢字 required
            5 => 'name_kana1', //氏名カナ
            6 => 'name_kana2', //氏名カナ
            8 => 'sex_type', //性別区分 required
            9 => 'birthday', //号生年月日
            10 => 'postal', //郵便番号 required
            11 => 'address1', //都道府県 required
            12 => 'address2', //市区町村 required
            13 => 'address3', //番地 required
            15 => 'address4', //建物名
            16 => 'tel1', //電話番号１ required
            18 => 'email1', //メールアドレス１
            20 => 'alert_cust_type',
            21 => 'dm_send_mail_flg', //DM配送方法 メール required
            22 => 'dm_send_letter_flg', //DM配送方法 郵便 required
            23 => 'customer_type', //顧客区分
            26 => 'delete_flg' // 使用区分 required
        ];

        // Fields to be cast to integers
        $intFields = ['sex_type', 'alert_cust_type', 'dm_send_mail_flg', 'dm_send_letter_flg', 'customer_type', 'delete_flg'];

        //get smallest m_itemname_types_id from m_itemname_type table
        $itemnametypeId = ItemnameTypeModel::where([
            'delete_flg' => 0, //[0:使用中]
            'm_account_id' => $accountId,
            'm_itemname_type' => ItemNameType::CustomerType->value //[15:顧客区分]
        ])->orderBy('m_itemname_type_sort', 'asc')->orderBy('m_itemname_types_id', 'asc')
            ->pluck('m_itemname_types_id')->first();

        // Create array of objects
        return array_map(function ($line) use ($keyMapping, $intFields, $itemnametypeId) {

            // Get the row values as an indexed array
            $row = str_getcsv($line, "\t");
            // when the column count doesn't match the Ecbeibg customer column count
            if (count($row) != self::ECBEING_CUST_COLUMN_COUNT) {
                // 取込ファイル内容フォーマットが間違っています。
                throw new Exception(__('messages.error.input_file_format_error'), self::PRIVATE_THROW_ERR_CODE);
            }

            $filteredRow = [];
            // Build the new key-value pairs based on the mapping
            foreach ($keyMapping as $index => $key) {
                if (isset($row[$index])) {
                    $filteredRow[$key] = $row[$index];
                }
            }

            // Concatenate name_kanji && name_kana
            $filteredRow['name_kanji'] = ($filteredRow['name_kanji1'] ?? '') . ($filteredRow['name_kanji2'] ?? '');
            $filteredRow['name_kana'] = ($filteredRow['name_kana1'] ?? '') . ($filteredRow['name_kana2'] ?? '');

            //remove all hyphens
            $filteredRow['postal'] = $filteredRow['postal'] ? str_replace('-', '', $filteredRow['postal']) : null;

            //any date format chnage to y-m-d
            $filteredRow['birthday'] = $filteredRow['birthday'] ? Carbon::parse($filteredRow['birthday'])->format('Y-m-d') : null;

            //該当カラムの項目名称マスタID (m_itemname_types_id) を 顧客マスタ.顧客区分 にセットする。
            $filteredRow['customer_type'] = $itemnametypeId;

            // Remove the unnecessary fields
            unset($filteredRow['name_kanji1'], $filteredRow['name_kanji2'], $filteredRow['name_kana1'], $filteredRow['name_kana2']);

            // Cast integer fields
            foreach ($intFields as $field) {
                if (isset($filteredRow[$field])) {
                    // Convert numeric or numeric string to integer, otherwise keep original value
                    $filteredRow[$field] = is_numeric($filteredRow[$field]) ? (int)$filteredRow[$field] : $filteredRow[$field];
                    // alert_cust_type change to null  when it is empty string
                    if ($filteredRow['alert_cust_type'] == '') {
                        $filteredRow['alert_cust_type'] = null;
                    }
                }
            }

            return $filteredRow;
        }, $lines);
    }

    /**
     * Customer Data validations and if validation fail export txt file for error message
     * @param array (customer data list)
     * @param string (account code)
     * @param string (batch type)
     * @return int (bath id)
     * @return mixed
     */

    private function validateCustomerData($dataList, $accountCode, $batchType, $bathID)
    {
        $request = new ImportEcbeingCustDataRequest();
        $rules = $request->rules(); // Get the validation rules from request

        foreach ($dataList as $rowNumber => $data) {
            $validator = Validator::make($data, $rules);

            // 顧客データ登録前のバリデーションエラー場合
            if ($validator->fails()) {
                // Collect all error details
                $failed = $validator->failed();
                $errorColumn = array_keys($failed)[0]; // error column
                $excecuteDateTime = Carbon::now()->toDateTimeString(); //batch excecute date time
                $errorMessage = $validator->errors()->first($errorColumn); //error message
                $errorRow = $rowNumber + 1; //error row
                $errorBathID = $bathID . "_error"; //custom error text file name

                // Format the data as a string
                $errorDetails =
                    "処理実行年月日時分秒: {$excecuteDateTime}\n" .
                    "バッチ実行番号: {$bathID}\n" .
                    "エラー原因: {$errorMessage}\n" .
                    "エラー発生時のcsv/エクセル行数: {$errorRow}\n" .
                    "エラー発生時の該当行データ: {$errorColumn}\n\n";

                //get tsv file path
                $savePath = $this->getTextExportFilePath->execute($accountCode, $batchType, $errorBathID);
                //save tsv file on s3
                $fileuploaded = Storage::disk($this->s3)->put($savePath, $errorDetails);

                //ファイルがAWS S3にアップロードされていない場合
                if (!$fileuploaded) {
                    //AWS S3へのファイルのアップロードに失敗しました。
                    throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
                }

                //取込ファイル内容のバリデーションチェックでエラーが発生しました。
                throw new Exception(__(
                    'messages.error.process_something_wrong2',
                    ['target' => '取込ファイル内容', 'process' => 'バリデーションチェック']
                ), self::PRIVATE_THROW_ERR_CODE);
            }
            return;
        }
    }

    /**
     * 更新時に[更新ユーザID]を追加します
     * 新規登録時に[企業アカウントID,登録ユーザID,更新ユーザID]を追加します
     * @param mixed (customer updateOrCreate return value)
     * @param int (account id)
     * @return int (operators id)
     */
    private function applyCustomerRecordUpdates($custRecord, $accountId, $operatorsId)
    {
        // 更新時に[更新ユーザID]を追加します
        $custRecord->update_operator_id = $operatorsId;
        // 新規登録時に[企業アカウントID,登録ユーザID,更新ユーザID]を追加します
        if ($custRecord->wasRecentlyCreated) {
            $custRecord->m_account_id = $accountId;
            $custRecord->entry_operator_id = $operatorsId;
        }
        $custRecord->save();
    }
}
