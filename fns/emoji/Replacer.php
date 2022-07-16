<?php

namespace Astrotomic\Emoji;

use Astrotomic\Emoji\Concerns\Configurable;

class Replacer
{
    use Configurable;

    public function text(string $text): EmojiText
    {
        return (new EmojiText($text))
            ->base($this->base)
            ->type($this->type);
    }
}
