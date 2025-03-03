<?php

namespace App\View\Components\Common;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;

class SortingLink extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $columnName,
        public $columnViewName,
        public $sortColumn,
        public $sortShift,
    )
    {
        //
        Log::debug('param', compact('columnName', 'columnViewName', 'sortColumn', 'sortShift'));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $isSortColumn = $this->sortColumn === $this->columnName;
        $direction = 'desc';
        if ($isSortColumn) {
            $direction = $this->sortShift === 'asc' ? 'desc' : 'asc';
        }
        return view('components.common.sorting-link', [
            'direction' => $direction,
            'sortArrow' => $isSortColumn ? ($direction === 'desc' ? '▲' : '▼') : '',
        ]);
    }
}
