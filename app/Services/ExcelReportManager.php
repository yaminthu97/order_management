<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Storage;

class ExcelReportManager
{
    private $spreadsheet;
    private $sheet;
    private $embedCell;                         // 単項目のセル情報

    private $continuousMergeCells = [];         // 連続項目の結合セル情報
    private $continuousEmbedCells = [];         // 連続項目のセル情報
    private $continuousStartRow = null;         // 連続項目の開始行
    private $continuousEndRow = null;           // 連続項目の終了行

    private $blockStartRow = null;              // ブロックエリアの開始行
    private $blockEndRow = null;                // ブロックエリアの終了行

    private $blockEmbedCells = [];              // ブロックエリアの単項目セル情報
    private $blockMergeCells = [];              // ブロックエリアの単項目の結合セル情報

    private $blockContinuousStartRow = null;    // ブロックエリアの連続項目開始行
    private $blockContinuousEndRow = null;      // ブロックエリアの連続項目終了行
    private $blockContinuousEmbedCells = [];    // ブロックエリアの連続項目セル情報
    private $blockContinuousMergeCells = [];    // ブロックエリアの連続項目の結合セル情報
    private $bracketsCells = [];

    protected $disk;
    // テンプレートファイルを読み込んで、埋め込み文字を解析する
    public function __construct($templateFile)
    {
        // filesystems の設定を取得する
        $this->disk = config('filesystems.default', 'local');

        $reader = new Xlsx();
        $reader->setIncludeCharts(true);

        // S3対応のためファイルを一時的にローカルに保存
        $tempFilePath = tempnam(sys_get_temp_dir(), 'xlsx');
        $fileContents = Storage::disk($this->disk)->get($templateFile);
        file_put_contents($tempFilePath, $fileContents);

        // ファイルを読み込み
        try {
            $this->spreadsheet = $reader->load($tempFilePath);
        } catch(\Exception $ex){
            // 一時ファイルの削除
            unlink($tempFilePath);
            throw $ex;
        }

        // 一時ファイルの削除
        unlink($tempFilePath);

        $this->sheet = $this->spreadsheet->getActiveSheet();    // アクティブシートのみを対象

        // テンプレートから単一項目の埋め込み文字のセル位置、項目名を取得する
        $this->embedCell = $this->getEmbedCell($this->sheet);

        // テンプレートから連続項目の埋め込み文字のセル位置、項目名、開始行、終了行を取得する
        $result = $this->getContinuousEmbedCell($this->sheet);
        $this->continuousEmbedCells = $result['embedCell'];
        $this->continuousStartRow = $result['startRow'];
        $this->continuousEndRow = $result['endRow'];

        // テンプレートからブロックエリアのエリア、埋め込み文字のセル位置、項目名、連続項目の開始行、終了行を取得する
        $result = $this->getBlockEmbedCell($this->sheet);
        $this->blockEmbedCells = $result['embedCells'];
        $this->blockStartRow = $result['startRow'];
        $this->blockEndRow = $result['endRow'];
        $this->blockContinuousEmbedCells = $result['continuousEmbedCells'];
        $this->blockContinuousStartRow = $result['continuousStartRow'];
        $this->blockContinuousEndRow = $result['continuousEndRow'];
        $this->blockMergeCells = $result['mergeCells'];
        $this->blockContinuousMergeCells = $result['continuousMergeCells'];

        // ブロックエリアが設定されていて、単一項目のセル位置がブロックエリアの最終行よりも大きければ、単一項目の行番号を2減じる
        if (!is_null($this->blockEndRow)) {
            for ($i = 0; $i < count($this->embedCell); $i++) {
                if ($this->embedCell[$i]['row'] > $this->blockEndRow) {
                    $this->embedCell[$i]['row'] -= 2;
                }
            }
        }

        // テンプレートから結合セルの情報を取得する
        // 連続項目の行内のもののみ、取得する
        $this->continuousMergeCells = $this->getMergeCells($this->sheet, $this->continuousStartRow, $this->continuousEndRow);

        // 結合セルの最大行番号が終了行よりも大きければ、終了行を上書きする
        $this->continuousEndRow = array_reduce($this->continuousMergeCells, function ($carry, $item) {
            list($start, $end) = explode(':', $item);
            $endCellRow = Coordinate::indexesFromString($end);
            return ($carry > $endCellRow[1]) ? $carry : $endCellRow[1];
        }, $this->continuousEndRow);
    }

    public function getContinuousEmbedCells()
    {
        return $this->continuousEmbedCells;
    }

    public function getActiveSheet()
    {
        return $this->sheet;
    }

    // 読み込んだ xlsx PhpOffice\PhpSpreadsheet\Spreadsheet instance で返す。
    public function getSpreadSheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    // データをワークシートに書き込む
    public function setValues($values, $continuousValues, $blockValues = null)
    {
        if (!is_null($values)) {
            $this->setEmbedCells($this->sheet, $this->embedCell, $values);
        }
        if (!is_null($continuousValues) && !is_null($this->continuousStartRow)) {
            $this->setContinuousEmbedCells($this->sheet, $this->continuousEmbedCells, $this->continuousStartRow, $this->continuousEndRow, $this->continuousMergeCells, $continuousValues);
        }
        if (!is_null($blockValues) && !is_null($this->blockStartRow)) {
            $this->setblockEmbedCells(
                $this->sheet,
                $this->blockStartRow,                  // ブロックエリアの開始行
                $this->blockEndRow,                    // ブロックエリアの終了行

                $this->blockEmbedCells,                // ブロックエリアの単項目セル情報
                $this->blockMergeCells,                // ブロックエリアの単項目の結合セル情報

                $this->blockContinuousStartRow,        // ブロックエリアの連続項目開始行
                $this->blockContinuousEndRow,          // ブロックエリアの連続項目終了行
                $this->blockContinuousEmbedCells,      // ブロックエリアの連続項目セル情報
                $this->blockContinuousMergeCells,      // ブロックエリアの連続項目の結合セル情報
                $blockValues
            );
        }
    }

    // Excelファイルを保存する
    public function save($outputFile)
    {
        // 一時ファイルに保存
        $writer = new XlsxWriter($this->spreadsheet);
        $writer->setIncludeCharts(true);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempFilePath);

        // ファイルの内容を取得して指定ディスクに保存
        $result = Storage::disk($this->disk)->put($outputFile, file_get_contents($tempFilePath));

        // 一時ファイルを削除
        unlink($tempFilePath);

        // 保存の成否を返す
        return $result;
    }
    public function saveLocalFile($filepath)
    {
        // 一時ファイルに保存
        $writer = new XlsxWriter($this->spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($filepath);
    }

    // 埋め込み文字のセル位置に値を設定する
    // 設定する値は引数から渡される
    // データ構造は次の通り
    // $data = array(
    //     // 埋め込み文字リスト
    //     item => array('name', 'age', 'city'),
    //     // データリスト
    //     data => array('John', 25, 'New York')
    // );
    // 引数から渡された値に対応する埋め込み文字がない場合は、値はセットしないでスキップする
    // 埋め込み文字のリストはgetEmbedCell()で取得したものとする
    private function setEmbedCells($sheet, $embedCells, $data)
    {
        foreach ($embedCells as $index => $embedCell) {
            $rowIndex = $embedCell['row'];
            $colIndex = $embedCell['column'];
            $cellName = $embedCell['name'];

            if (in_array($cellName, $data['items'])) {
                $valueIndex = array_search($cellName, $data['items']);
                $value = $data['data'][$valueIndex];
                $sheet->setCellValue($colIndex . $rowIndex, $value);
            }
        }
    }

    // 連続項目の埋め込み文字のセル位置に値を設定する
    // 設定する値は引数から渡される
    // 引数で渡される値は、複数組のデータを持つ配列とする
    // 連続項目の埋め込み文字のセル位置は、１組目のデータの位置なので
    // 2組目以降のデータは、必要な行数を挿入した後に値をセットする
    // 引数から渡された値に対応する梅子文字がない場合は、値はセットしないでスキップする
    private function setContinuousEmbedCells($sheet, $embedCells, $startRow, $endRow, $mergeCells, $data)
    {

        $valueIndex = [];
        foreach ($embedCells as $index => $embedCell) {

            if (in_array($embedCell['name'], $data['items'])) {
                $valueIndex[] = [
                    'name'          => $embedCell['name'],
                    'column'        => $embedCell['column'],
                    'row'           => $embedCell['row'],
                    'index'     => array_search($embedCell['name'], $data['items'])
                ];
            }
        }

        $rowDiff = $endRow - ($startRow - 1);
        for ($i = 0; $i < count($data['data']); $i++) {
            if ($i > 0) {
                $sheet->insertNewRowBefore($startRow + ($rowDiff * $i), $rowDiff);
                $this->duplicateRows($sheet, $startRow, $startRow + ($rowDiff * $i), $rowDiff);
                $this->setMergeCells($sheet, $mergeCells, $rowDiff * $i);
            }

            foreach ($valueIndex as $x) {
                $range = $x['column'] . $x['row'] + ($rowDiff * $i);
                $sheet->setCellValue($range, $data['data'][$i][$x['index']]);
            }
        }
    }

    // テンプレートの中で連続項目のベース行をコピーする
    // 連続項目以外のセルをコピーすることが目的
    private function duplicateRows($sheet, $source, $target, $rowCount)
    {
        // 高さの調整
        for ($i = 0; $i < $rowCount; $i++) {
            $sheet->getRowDimension($target + $i)->setRowHeight($sheet->getRowDimension($source + $i)->getRowHeight());
        }
        // //コピー元セル範囲の開始、終了位置をインデックスで取得
        // [$start, $end] = Coordinate::rangeBoundaries($fromAddress);
        // //コピー先セルの位置をインデックスで取得
        // $dustRow = (int)Coordinate::rangeBoundaries($distAddress)[0][1];

        // //書式のコピー
        // foreach (range((int)$start[1], (int)$end[1]) as $index => $row) {
        //     foreach (range($start[0], $end[0]) as $col) {
        //         //コピー元のセルスタイル取得
        //         $style = $sheet->getStyle([$col, $row, $col, $row]);
        //         //コピー先のセルにスタイルをコピー
        //         $sheet->duplicateStyle($style,
        //             Coordinate::stringFromColumnIndex($col).strval($dustRow + $index));
        //     }
        // }

        foreach ($sheet->getRowIterator($source, ($source + $rowCount) - 1) as $row) {
            if ($row->isEmpty()) {
                continue;   // Ignore empty rows
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $diff = $target + ($cell->getRow() - $source);
                $c = $cell->getColumn();
                $r = $cell->getRow();
                $range = $c . $diff;

                // 書式とスタイルを取得
                $formatCode = $cell->getStyle()->getNumberFormat()->getFormatCode();
                $style = $sheet->getStyle($c . $r);

                //コピー先のセルにスタイルをコピー
                $sheet->duplicateStyle($style, $range);
                $sheet->getStyle($range)->getNumberFormat()->setFormatCode($formatCode);
                $sheet->setCellValue($c . $diff, $cell->getFormattedValue());
            }
        }
    }

    // 連続項目の埋め込み文字に結合セルがある場合は、挿入した行に結合セルを設定する
    private function setMergeCells($sheet, $mergeCells, $rows)
    {
        foreach ($mergeCells as $cells) {
            list($start, $end) = explode(':', $cells);
            $startCellRow = Coordinate::indexesFromString($start);
            $endCellRow = Coordinate::indexesFromString($end);

            $mergeCell =
                $startCellRow[2] .
                ($startCellRow[1] + $rows) .
                ':' .
                $endCellRow[2] .
                ($endCellRow[1] + $rows);

            $sheet->mergeCells($mergeCell);
        }
    }

    // ブロックエリアの埋め込み文字のセル位置に値を設定する
    private function setBlockEmbedCells(
        $sheet,
        $blockStartRow,                  // ブロックエリアの開始行
        $blockEndRow,                    // ブロックエリアの終了行

        $blockEmbedCells,                // ブロックエリアの単項目セル情報
        $blockMergeCells,                // ブロックエリアの単項目の結合セル情報

        $blockContinuousStartRow,        // ブロックエリアの連続項目開始行
        $blockContinuousEndRow,          // ブロックエリアの連続項目終了行
        $blockContinuousEmbedCells,      // ブロックエリアの連続項目セル情報
        $blockContinuousMergeCells,      // ブロックエリアの連続項目の結合セル情報
        $data
    ) {
        // ブロックのデータ件数単位にブロックの行数を挿入する
        // 最終データから挿入していく（連続項目の行数が可変なため）
        $startRow = $blockStartRow;
        $endRow = $blockEndRow;
        $rowDiff = $endRow - $startRow;

        $singleValueIndex = [];
        foreach ($blockEmbedCells as $index => $embedCell) {
            if (in_array($embedCell['name'], $data['singleItems'])) {
                $singleValueIndex[] = [
                    'name'          => $embedCell['name'],
                    'column'        => $embedCell['column'],
                    'row'           => $embedCell['row'],
                    'index'     => array_search($embedCell['name'], $data['singleItems'])
                ];
            }
        }

        $listValueIndex = [];
        foreach ($blockContinuousEmbedCells as $index => $embedCell) {
            if (in_array($embedCell['name'], $data['listItems'])) {
                $listValueIndex[] = [
                    'name'          => $embedCell['name'],
                    'column'        => $embedCell['column'],
                    'row'           => $embedCell['row'],
                    'index'     => array_search($embedCell['name'], $data['listItems'])
                ];
            }
        }

        for ($i = count($data['data']) - 1; $i >= 0; $i--) {
            $d = $data['data'][$i];

            // 行の挿入
            $sheet->insertNewRowBefore($startRow + $rowDiff, $rowDiff);
            // 挿入した行のコピー
            $this->duplicateRows($sheet, $startRow, $startRow + $rowDiff, $rowDiff);
            // 結合セルの状態をコピー
            $this->setMergeCells($sheet, $blockMergeCells, $rowDiff);

            // 単項目
            $this->setSingleItemBlock($sheet, $singleValueIndex, $rowDiff, $d);

            // 連続項目
            $this->setListItemBlock(
                $sheet,
                $listValueIndex,
                $blockContinuousStartRow,
                $blockContinuousEndRow,
                $rowDiff,
                $blockContinuousMergeCells,
                $d
            );
        }

        // テンプレートのブロック行を削除する
        $sheet->removeRow($startRow, $rowDiff);
    }

    private function setSingleItemBlock($sheet, $singleValueIndex, $rowDiff, $data)
    {
        // 単項目
        foreach ($singleValueIndex as $x) {
            $range = $x['column'] . $x['row'] + $rowDiff;
            $sheet->setCellValue($range, $data['singleData'][$x['index']]);
        }
    }

    private function setListItemBlock($sheet, $listValueIndex, $continuousStartRow, $continuousEndRow, $rowDiff, $mergeCells, $data)
    {
        $listRowDiff = $continuousEndRow - ($continuousStartRow - 1);
        $startRow = $continuousStartRow + $rowDiff;
        for ($i = 0; $i < count($data['listData']); $i++) {
            if ($i > 0) {
                // 行挿入
                $sheet->insertNewRowBefore($startRow + ($listRowDiff * $i), $listRowDiff);
                // 行コピー
                $this->duplicateRows($sheet, $startRow, $startRow + ($listRowDiff * $i), $listRowDiff);
            }

            // 結合セルの状態を反映
            $this->setMergeCells($sheet, $mergeCells, $rowDiff + ($listRowDiff * $i));
            foreach ($listValueIndex as $x) {
                $range = $x['column'] . $x['row'] + $rowDiff + ($listRowDiff * $i);
                $sheet->setCellValue($range, $data['listData'][$i][$x['index']]);
            }
        }
    }

    // 各シートの埋め込み文字のセル位置を取得する
    // 埋め込み文字の書式は${xxxx}とする
    private function getEmbedCell($sheet)
    {
        $embedCell = [];
        foreach ($sheet->getRowIterator() as $row) {
            if ($row->isEmpty()) {
                continue;   // Ignore empty rows
            }

            foreach ($sheet->getColumnIterator() as $column) {
                if ($sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getDataType() == 'null') {
                    continue;
                }
                $value = $sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getFormattedValue();
                if (preg_match('/\${([^{].+?)}/', $value, $matches)) {
                    $embedCell[] = [
                        'row' => $row->getRowIndex(),
                        'column' => $column->getColumnIndex(),
                        'name' => $matches[1]
                    ];
                }
            }
        }
        return $embedCell;
    }

    // 各シートの埋め込み文字のセル位置を取得する
    // 埋め込み文字の書式は$[xxxx]とする
    public function getBracketsCell()
    {
        if (0 < count($this->bracketsCells)) {
            return $this->bracketsCells;
        }

        $sheet = $this->sheet;

        foreach ($sheet->getRowIterator() as $row) {
            if ($row->isEmpty()) {
                continue;   // Ignore empty rows
            }

            foreach ($sheet->getColumnIterator() as $column) {
                $cellPos = $column->getColumnIndex() . $row->getRowIndex();
                $cell = $sheet->getCell($cellPos);

                if ($cell->getDataType() == 'null') {
                    continue;
                }

                $value = $cell->getFormattedValue();

                if (preg_match('/\$\[([^\[].+?)\]/', $value, $matches)) {
                    $this->bracketsCells[] = [
                        'row' => $row->getRowIndex(),
                        'column' => $column->getColumnIndex(),
                        'name' => $matches[1]
                    ];

                    $sheet->setCellValue($cellPos, '');
                }
            }
        }

        return $this->bracketsCells;
    }

    // 連続項目の埋め込み文字のセル位置を取得する
    // 連続項目の埋め込み文字の書式は${{xxxx}}とする
    // 連続項目の埋め込み文字の中から、一番最初の行番号と最後の行番号も取得する
    private function getContinuousEmbedCell($sheet)
    {
        $embedCell = [];
        $startRow = null;
        $endRow = null;
        foreach ($sheet->getRowIterator() as $row) {
            if ($row->isEmpty()) {
                continue;   // Ignore empty rows
            }

            foreach ($sheet->getColumnIterator() as $column) {
                if ($sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getDataType() == 'null') {
                    continue;
                }
                $cell = $sheet->getCell($column->getColumnIndex() . $row->getRowIndex());
                $value = $cell->getFormattedValue();
                if (preg_match('/\${{(.+?)}}/', $value, $matches)) {
                    if ($startRow === null) {
                        $startRow = $row->getRowIndex();
                    }
                    $endRow = $row->getRowIndex();
                    $embedCell[] = [
                        'row' => $row->getRowIndex(),
                        'column' => $column->getColumnIndex(),
                        'name' => $matches[1],
                    ];
                }
            }
        }
        return ['embedCell' => $embedCell, 'startRow' => $startRow, 'endRow' => $endRow];
    }

    // ブロックエリアの埋め込み文字情報を取得する
    private function getBlockEmbedCell($sheet)
    {
        $embedCells = [];
        $startRow = 0;
        $endRow = 0;
        $continuousEmbedCells = [];
        $continuousStartRow = 0;
        $continuousEndRow = 0;

        $mergeCells = [];
        $continuousMergeCells = [];

        // ブロックエリアを取得する
        // $<block_start>から$<block_end>が含まれる行番号を取得する
        foreach ($sheet->getRowIterator() as $row) {
            if ($row->isEmpty()) {
                continue;   // Ignore empty rows
            }

            foreach ($sheet->getColumnIterator() as $column) {
                if ($sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getDataType() == 'null') {
                    continue;
                }
                $value = $sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getFormattedValue();
                if (strpos($value, '$<block_start>') !== false) {
                    $startRow = $row->getRowIndex();
                    $sheet->removeRow($row->getRowIndex(), 1);
                }

                if (strpos($value, '$<block_end>') !== false) {
                    $endRow = $row->getRowIndex();
                    $sheet->removeRow($row->getRowIndex(), 1);
                }
            }
        }

        // ブロックエリアの整合性を確認する
        // 開始行の位置が終了行の位置よりも大きい場合は、無効として以降の処理を行わない
        if ($startRow > $endRow) {
            return [
                'embedCells'            => [],
                'startRow'              => null,
                'endRow'                => null,
                'continuousEmbedCells'  => [],
                'continuousStartRow'    => null,
                'continuousEndRow'      => null,
                'mergeCells'            => [],
                'continuousMergeCells'  => []
            ];
        }

        $singleStartRow = 0;
        $singleEndRow = 0;

        // ブロックエリア内の単項目、連続綱目の項目名とセル位置を取得する
        foreach ($sheet->getRowIterator() as $row) {
            if ($row->isEmpty() || $row->getRowIndex() < $startRow) {
                continue;
            }
            if ($row->getRowIndex() > $endRow) {
                break;
            }

            foreach ($sheet->getColumnIterator() as $column) {
                if ($sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getDataType() == 'null') {
                    continue;
                }
                $cell = $sheet->getCell($column->getColumnIndex() . $row->getRowIndex());
                $value = $sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getFormattedValue();
                // 単項目 $<...>
                if (preg_match('/\$<([^<].+?)>/', $value, $matches)) {
                    if ($singleStartRow == 0) {
                        $singleStartRow = $row->getRowIndex();
                    }
                    $singleEndRow = $row->getRowIndex();
                    $embedCells[] = [
                        'row' => $singleEndRow,
                        'column' => $column->getColumnIndex(),
                        'name' => $matches[1],
                    ];
                }
                // 連続項目 $<<...>>
                else if (preg_match('/\$<<(.+?)>>/', $value, $matches)) {
                    if ($continuousStartRow === 0) {
                        $continuousStartRow = $row->getRowIndex();
                    }
                    $continuousEndRow = $row->getRowIndex();
                    $continuousEmbedCells[] = [
                        'row' => $row->getRowIndex(),
                        'column' => $column->getColumnIndex(),
                        'name' => $matches[1],
                    ];
                }
            }
        }

        // 単項目ブロックと連続項目ブロックの行がが重なっている場合は、エラーとする
        if (
            ($singleStartRow <= $continuousStartRow && $singleEndRow <= $continuousEndRow && $singleEndRow >= $continuousStartRow) ||
            ($continuousStartRow <= $singleStartRow && $continuousEndRow <= $singleEndRow && $continuousEndRow >= $singleStartRow) ||
            ($continuousStartRow <= $singleStartRow && $continuousEndRow >= $singleEndRow)
        ) {
            return [
                'embedCells'            => [],
                'startRow'              => null,
                'endRow'                => null,
                'continuousEmbedCells'  => [],
                'continuousStartRow'    => null,
                'continuousEndRow'      => null,
                'mergeCells'            => [],
                'continuousMergeCells'  => []
            ];
        }

        // ブロック内の結合セル情報を取得する
        // 前提条件；ブロック内の全ての単項目は連続項目よりも前の行に配置されている必要がある

        // 単項目のブロックの後に連続項目のブロックがある場合
        if (
            ($singleEndRow < $continuousStartRow) ||
            ($singleStartRow > $continuousEndRow)
        ) {
            $mergeCells = $this->getMergeCells($sheet, $singleStartRow, $singleEndRow);
            $continuousMergeCells = $this->getMergeCells($sheet, $continuousStartRow, $continuousEndRow);
        }
        // 単項目のブロック内に連続項目のブロックがある場合
        else {
            $continuousMergeCells = $this->getMergeCells($sheet, $continuousStartRow, $continuousEndRow);
            $mergeCellsBefore = $this->getMergeCells($sheet, $singleStartRow, $continuousStartRow);
            $mergeCellsAfter = $this->getMergeCells($sheet, $continuousEndRow + 1, $singleEndRow);
            $mergeCells = array_merge($mergeCellsBefore, $mergeCellsAfter);
        }

        // 連続項目部分
        return [
            'embedCells'            => $embedCells,
            'startRow'              => $startRow,
            'endRow'                => $endRow,
            'continuousEmbedCells'  => $continuousEmbedCells,
            'continuousStartRow'    => $continuousStartRow,
            'continuousEndRow'      => $continuousEndRow,
            'mergeCells'            => $mergeCells,
            'continuousMergeCells'  => $continuousMergeCells
        ];
    }

    // 結合セルの情報を取得する
    // 引数で渡された行の範囲内のもののみ取得する
    private function getMergeCells($sheet, $startRow, $endRow)
    {
        $mergeCells = [];
        foreach ($sheet->getMergeCells() as $mergeCell) {
            $range = Coordinate::rangeBoundaries($mergeCell);
            if ($range[0][1] == $startRow) {
                $mergeCells[] = $mergeCell;
            } elseif ($range[0][1] > $startRow && $range[0][1] <= $endRow) {
                $mergeCells[] = $mergeCell;
            }
        }
        return $mergeCells;
    }
}
