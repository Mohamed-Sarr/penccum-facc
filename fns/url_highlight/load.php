<?php
include('fns/url_highlight/autoload.php');

use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

class CustomURLHighlighter extends HtmlHighlighter
{
    protected function getText(UrlMatch $match): string
    {
        return $match->getHost();
    }
}