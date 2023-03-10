<?php

namespace Kiwilan\Steward\Services\Factory;

use Kiwilan\Steward\Enums\FactoryTextEnum;
use Kiwilan\Steward\Services\FactoryService;

/**
 * Generate fake rich text.
 */
class FactoryRichText
{
    public function __construct(
        public FactoryService $factory,
        public FactoryTextEnum $type,
        protected FactoryText $text,
        public string $typography = 'html',
        protected int $min = 1,
        protected int $max = 5,
        protected bool $image = true,
        protected bool $link = true,
        protected bool $extra = true,
    ) {
    }

    public function useHtml(): self
    {
        $this->typography = 'html';

        return $this;
    }

    public function useMarkdown(): self
    {
        $this->typography = 'markdown';

        return $this;
    }

    public function useText(): self
    {
        $this->typography = 'text';

        return $this;
    }

    public function setMin(int $min = 1): self
    {
        $this->min = $min;

        return $this;
    }

    public function setMax(int $max = 5): self
    {
        $this->max = $max;

        return $this;
    }

    public function noImage(): self
    {
        $this->image = false;

        return $this;
    }

    public function noLink(): self
    {
        $this->link = false;

        return $this;
    }

    public function noExtra(): self
    {
        $this->extra = false;

        return $this;
    }

    /**
     * Generate paragraphs.
     */
    public function paragraphs(): string
    {
        $content = null;

        for ($k = 0; $k < $this->factory->faker()->numberBetween($this->min, $this->max); $k++) {
            if ($this->typography === 'html') {
                $content .= "{$this->html()}<br><br>";
            }

            if ($this->typography === 'markdown') {
                $content .= "{$this->markdown()}\n\n";
            }

            if ($this->typography === 'text') {
                $content .= "{$this->text->paragraph()}<br><br>";
            }

            if (! $content) {
                throw new \Exception("`typography` must be `html` or `markdown` or `text` but is `{$this->typography}`");
            }
        }

        return $content;
    }

    /**
     * Generate a HTML paragraph.
     */
    private function html(): string
    {
        $html = '';

        for ($k = 0; $k < $this->factory->faker()->numberBetween(2, 5); $k++) {
            $paragraph = $this->text->paragraph();

            if ($this->factory->faker()->boolean(25)) {
                $paragraph .= " <strong>{$this->text->sentence()}</strong>";
            }

            if ($this->factory->faker()->boolean(25)) {
                $paragraph .= " <em>{$this->text->sentence()}</em>";
            }

            if ($this->factory->faker()->boolean(25)) {
                $paragraph .= " <code>{$this->text->words()}</code>";
            }

            if ($this->link && $this->factory->faker()->boolean(25)) {
                $paragraph .= " <a href=\"{$this->factory->faker()->url()}\">{$this->text->words()}</a>";
            }

            if ($this->image && $this->factory->faker()->boolean(15)) {
                $paragraph = "<a href=\"{$this->text->imageUrl()}\" target=\"_blank\"><img src=\"{$this->text->imageUrl()}\" alt=\"{$this->text->sentence()}\" /></a>";
            }
            $html .= "<p>{$paragraph}</p>";
        }

        return $html;
    }

    /**
     * Generate a Markdown paragraph.
     */
    private function markdown()
    {
        $bold_text = " **{$this->text->sentence()}** ";
        $italic_text = " *{$this->text->sentence()}* ";
        $code_text = " `{$this->text->words()}` ";
        $link_text = " [{$this->text->sentence()}]({$this->factory->faker()->url()}) ";
        $image_text = "  ![{$this->text->sentence()}]({$this->text->imageUrl()})  ";

        $html = [];

        if ($this->factory->faker()->boolean(25)) {
            $html[] = $bold_text;
        }

        if ($this->factory->faker()->boolean(25)) {
            $html[] = $italic_text;
        }

        if ($this->extra && $this->factory->faker()->boolean(25)) {
            $html[] = $code_text;
        }

        if ($this->link && $this->factory->faker()->boolean(25)) {
            $html[] = $link_text;
        }

        if ($this->image && $this->factory->faker()->boolean(25)) {
            $html[] = $image_text;
        }

        for ($k = 0; $k < $this->factory->faker()->numberBetween($this->min, $this->max); $k++) {
            $paragraph = $this->text->sentence();
            $html[] = "{$paragraph}";
        }

        for ($k = 0; $k < $this->factory->faker()->numberBetween($this->min, $this->max); $k++) {
            $paragraph = $this->text->paragraph();
            $html[] = "{$paragraph}";
        }

        shuffle($html);
        $html = preg_replace('/\s\s+/', ' ', $html);

        return implode('', $html);
    }
}
