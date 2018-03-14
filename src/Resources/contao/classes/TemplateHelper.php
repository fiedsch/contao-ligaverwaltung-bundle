<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

class TemplateHelper
{

    /**
     * Render an array as List (<ul></ul>). The list will be nested
     * if the data array is multidimensional.
     *
     * @param array $data
     * @return string
     */
    public static function renderArrayAsList($data)
    {
        if (!is_array($data)) {
            return sprintf('<li>%s</li>', $data);
        }
        $result = '<ul>';
        foreach ($data as $item) {
            $result .= self::renderArrayAsList($item);
        }
        $result .= '</ul>';
        return $result;
    }

}