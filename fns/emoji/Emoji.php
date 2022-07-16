<?php

namespace Astrotomic\Emoji;

use Astrotomic\Emoji\Concerns\Configurable;
use JsonSerializable;

class Emoji implements JsonSerializable
{
    use Configurable;

    public const SVG = 'svg';
    public const PNG = 'png';

    /** @var string[] */
    protected array $codepoints;

    /**
    * @param string[] $codepoints
    */
    public function __construct(array $codepoints) {
        $this->codepoints = $codepoints;
    }

    public static function emoji(string $emoji): self
    {
        $chars = preg_split('//u', $emoji, null, PREG_SPLIT_NO_EMPTY);

        $codepoints = array_map(
            fn (string $code): string => dechex(mb_ord($code)),
            $chars
        );

        $normalized = array_diff($codepoints, ['fe0f']);

        return new static($normalized);
    }

    public static function text(string $text): EmojiText
    {
        return new EmojiText($text);
    }

    public function url(): string
    {
        $site_url = 'files/emojis';
        return sprintf(
            '%s/%s/%s.%s',
            $this->base,
            $this->type === self::PNG ? $site_url : 'svg',
            implode('-', $this->codepoints),
            $this->type
        );
    }

    public function jsonSerialize() {
        return $this->url();
    }

    public function __toString(): string
    {
        return $this->url();
    }
}