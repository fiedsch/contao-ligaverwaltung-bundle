<?php

namespace Fiedsch\Ligaverwaltung\Helper;

class UrlHelper
{
    /**
     * "Translate" an URL with id parameter to a folder url style URL.
     * Example:
     * original url
     *     http://contao5.local/spielbericht.html?id=9045
     * result will be
     *     http://contao5.local/spielbericht/9045.html
     *
     * Note: this is just a hack that should be removed, once the url generation is properly implemented (by us)!
     */
    public static function asFolderUrl(string $url, string $parameterName = 'id', string $urlSuffix = '.html'): string
    {
        $pattern = sprintf('/^(.+)%s\?%s=(\d+)$/',  $urlSuffix, $parameterName);
        preg_match($pattern, $url, $matches);
        if ($matches) {
            return sprintf('%s/%d.html', $matches[1], $matches[2]);
        }

        return $url;
    }
}
