<?php
namespace App\Exceptions;

use Exception;

class ModuleValidationException extends Exception
{
    //put your code here

    protected $validationErrors =[];

    /**
     * @param string $validationErrors [field => [error1 => message1, error2 => message2]]
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, $validationErrors = []) {
        parent::__construct($message, $code, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors() {
        return $this->validationErrors;
    }
}
