<?php

/**
* @var $draw
 * */

$content = '<p>';
$content .= sprintf(
    __("Do you need help? Check the %s or contact %s.", WOOZASILKOVNASLUG),
    '<a href="https://documentation.toret.cz/zasilkovna" target="_blank" rel="noopener noreferrer">' . __("documentation", WOOZASILKOVNASLUG) . '</a>',
    '<a href="https://toret.cz/podpora/" target="_blank" rel="noopener noreferrer">' . __("support", WOOZASILKOVNASLUG) . '</a>'
);
$content .= '</p>';

$draw->draw_settings_box(
    $content,
    '',
    false
);

