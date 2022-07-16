<?php

namespace Astrotomic\Emoji\Concerns;

use Astrotomic\Emoji\Emoji;

trait Configurable
{
    protected string $type = Emoji::SVG;

    protected string $base = 'assets';

    public function base(string $base): self
    {
        $this->base = rtrim($base, '/');

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function svg(): self
    {
        $this->type = Emoji::SVG;

        return $this;
    }

    public function png(): self
    {
        $this->type = Emoji::PNG;

        return $this;
    }
}
