<?php

namespace App\View\Components\Common;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OutputCheckBox extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $name,
        public string $keyValue,
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.common.output-check-box');
    }
}
