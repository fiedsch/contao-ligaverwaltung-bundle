<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016- Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Ligaverwaltung\Helper;

use function is_array;

class TemplateHelper
{
    /**
     * Render an array as List (<ul></ul>). The list will be nested
     * if the data array is multidimensional.
     */
    public static function renderArrayAsList(array $data): string
    {
        $result = '<ul>';

        foreach ($data as $item) {
            $result .= self::renderArrayItemAsList($item);
        }
        $result .= '</ul>';

        return $result;
    }

    protected static function renderArrayItemAsList(string|array $data): string
    {
        if (!is_array($data)) {
            return sprintf('<li>%s</li>', $data);
        }
        return self::renderArrayAsList($data);
    }
}
