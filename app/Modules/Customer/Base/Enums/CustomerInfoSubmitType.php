<?php
namespace App\Modules\Customer\Base\Enums;

enum CustomerInfoSubmitType:string
{
    case MAILNEW = 'mailnew';
    case CUSTOMERHISTORYNEW = 'customerhistorynew';
    case ORDERNEW = 'ordernew';
    case CCCUSTOMERMAIL = 'cccustomermail';
    case CCCUSTOMERORDER = 'cccustomerorder';
    case CUSTOMERHISTORYLIST = 'customerhistorylist';
    case CUSTOMEREDIT = 'customeredit';
    case CCCUSTOMERLIST = 'cccustomerlist';
}
