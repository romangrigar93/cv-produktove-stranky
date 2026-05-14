<?php

namespace ToretZasilkovna\Toret\Library;

class LibraryManager
{

    const TORET_LIBRARY_VERSION = '1.0.0';
    const TORET_LIBRARY_PREFIX = 'ToretZasilkovna';
    const TORET_LIBRARY_SETTINGS_PREFIX = 'toret-';

    public function __construct()
    {
        $this->init();
        $this->load();
    }

    private function init()
    {
        require_once __DIR__ . '/Dimensions.php';
        require_once __DIR__ . '/Draw.php';
        require_once __DIR__ . '/ExchangeRates.php';
        require_once __DIR__ . '/SettingsFieldsTrait.php';
        require_once __DIR__ . '/Settings.php';
        require_once __DIR__ . '/Form.php';
        require_once __DIR__ . '/Helper.php';
        require_once __DIR__ . '/Log.php';
        require_once __DIR__ . '/Popup.php';
        require_once __DIR__ . '/Taxes.php';
        require_once __DIR__ . '/Date.php';
        require_once __DIR__ . '/Text.php';
        require_once __DIR__ . '/Shipping.php';
        require_once __DIR__ . '/HPOS.php';
        require_once __DIR__ . '/functions.php';
    }

    private function load()
    {
        new Draw();
        new Settings();
    }
}