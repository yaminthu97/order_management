<?php

namespace App\View\Components\Common;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;

class PageListCount extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $pageListCount,
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        Log::info('pageListCount: ' . $this->pageListCount);
        $pageCountList = config('Common.const.disp_limits');
        return view('components.common.page-list-count', [
            'pageCountList' => $pageCountList,
        ]);
    }
}
