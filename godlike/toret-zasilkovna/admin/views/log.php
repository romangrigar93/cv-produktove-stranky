<?php

use ToretZasilkovna\Toret\Library\Log;

$ToretZasilkovnaLog = new Log(WOOZASILKOVNASLUG,TORETZASILKOVNALOGTABLE, TORETZASILKOVNALOGTABLEVERSION);
$ToretZasilkovnaLog->renderLogPage();