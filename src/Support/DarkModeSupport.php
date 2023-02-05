<?php

namespace Kiwilan\Steward\Support;

class DarkModeSupport
{
    public function embed(): string
    {
        return <<<'HTML'
        <script>
            if (colorScheme) {
                document.documentElement.classList.toggle(colorScheme, true)
            } else {
                const system = window.matchMedia &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches ?
                    'dark' :
                    'light'
                document.documentElement.classList.toggle(system, true)
            }
        </script>
        HTML;
    }
}
