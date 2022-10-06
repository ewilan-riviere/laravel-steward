<?php

namespace Kiwilan\Steward\Components;

use Illuminate\View\Component;

class FieldText extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $name = 'text',
        public string $type = 'text',
        public string $label = '',
        public string $value = '',
        public string $placeholder = '',
        public string $helper = '',
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $multiline = false,
        public ?string $prefix = null,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('steward::components.field.text');
    }
}
