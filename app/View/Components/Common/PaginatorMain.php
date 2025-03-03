<?php

namespace App\View\Components\Common;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PaginatorMain extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $paginator
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.common.paginator-main');
    }
}
