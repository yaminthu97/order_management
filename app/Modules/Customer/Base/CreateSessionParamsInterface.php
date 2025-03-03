<?php
namespace App\Modules\Customer\Base;

interface CreateSessionParamsInterface
{
    public function execute(array $params): array;
}
