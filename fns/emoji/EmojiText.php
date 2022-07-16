<?php

namespace Astrotomic\Emoji;

use Astrotomic\Emoji\Concerns\Configurable;
use Closure;

/**
* @internal
*/
class EmojiText
{
    use Configurable;

    protected $text;

    public function __construct(string $text) {
        $ascii_emojis = [
            ':\)' => '🙂',
            ':D' => '😄',
            ':\(' => '☹️',
            ';\(' => '😢',
            ':O(?!\S)' => '😮',
            ':o' => '😮',
            ':*' => '😘',
            ':P' => '😜',
            ':p' => '😜',
            ';\)' => '😉',
            ':\|' => '😐',
        ];
        foreach ($ascii_emojis as $ascii_emoji => $replacewith) {
            $regex = '#(?<!\S)('.$ascii_emoji.')(?!\S)#iu';
            $text = preg_replace($regex, $replacewith, $text);
        }
        //$text = preg_replace(array_keys($ascii_emojis), array_values($ascii_emojis), $text);
        $this->text = $text;
    }

    public function toMarkdown(?Closure $alt = null): string
    {
        return $this->replace('![%{alt}](%{src})', $alt);
    }

    public function toHtml(?Closure $alt = null, array $attributes = [], $url = null): string
    {
        $attributes = array_merge([
            'width' => 20,
            'height' => 20,
        ], $attributes);

        $attrs = implode(' ', array_map(
            fn (string $key, string $value): string => "{$key}=\"{$value}\"",
            array_keys($attributes),
            array_values($attributes)
        ));

        return $this->replace('<img src="'.$url.'%{src}" alt="%{alt}" '.$attrs.' />', $alt);
    }

    protected function replace(string $replacement, ?Closure $alt = null): string
    {
        $text = $this->text;

        $text = preg_replace_callback(
            $this->regexp(),
            fn (array $matches): string => str_replace(
                ['%{alt}', '%{src}'],
                [
                    $alt
                    ? $alt($matches[0])
                    : $matches[0],
                    Emoji::emoji($matches[0])
                    ->base($this->base)
                    ->type($this->type)
                    ->url(),
                ],
                $replacement
            ),
            $text
        );

        return $text;
    }

    protected function regexp(): string
    {
        return '/(?:'.json_decode(file_get_contents(dirname(__FILE__).'/regexp.json')).')/u';
    }
}