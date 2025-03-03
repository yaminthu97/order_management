<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\TsvFormatCheckInterface;

class TsvFormatCheck implements TsvFormatCheckInterface
{
    /**
     * check tsv data format
     * @param string (tsv data)
     * @return  bool
     */
    public function execute($tsvData)
    {
        // Check if the input is a string
        if (!is_string($tsvData)) {
            return false;
        }
        // Split the data into rows using newline characters
        $rows = preg_split('/\r\n|\n|\r/', trim($tsvData));

        // Check if each row contains tab-separated values
        foreach ($rows as $row) {
            // Validate if the row contains at least one tab character
            if (strpos($row, "\t") === false) {
                return false;
            }
        }
        return true;
    }
}
