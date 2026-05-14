<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class ToretZasilkovnaLib
{
    /**
     * @var ToretZasilkovnaHelper
     */
    public ToretZasilkovnaHelper $Helper;

    /**
     * @var ToretZasilkovnaOutputs
     */
    public ToretZasilkovnaOutputs $Outputs;

    /**
     * @var ToretZasilkovnaSendTicket
     */
    public ToretZasilkovnaSendTicket $Send;

    /**
     * @var ToretZasilkovnaClaimTicket
     */
    public ToretZasilkovnaClaimTicket $Claim;

    /**
     * @var ToretZasilkovnaDimensionHelper
     */
    public ToretZasilkovnaDimensionHelper $DimensionHelper;

    /**
     * @var ToretZasilkovnaCustoms
     */
    public ToretZasilkovnaCustoms $Customs;

    public function __construct(){
        $this->init();
    }

    private function init(){
        $this->IncludeClasses();
        $this->CreateCall();
    }

    private function IncludeClasses(){
        include_once('ToretZasilkovnaHelper.php');
        include_once('ToretZasilkovnaOutputs.php');
        include_once('ToretZasilkovnaSendTicket.php');
        include_once('ToretZasilkovnaClaimTicket.php');
        include_once('ToretZasilkovnaDimensionHelper.php');
        include_once('ToretZasilkovnaCustoms.php');
    }

    private function CreateCall(){
        $this->Helper = new ToretZasilkovnaHelper();
        $this->Outputs = new ToretZasilkovnaOutputs();
        $this->Send = new ToretZasilkovnaSendTicket();
        $this->Claim = new ToretZasilkovnaClaimTicket();
        $this->DimensionHelper = new ToretZasilkovnaDimensionHelper();
        $this->Customs = new ToretZasilkovnaCustoms();
    }
}

function ToretZasilkovnaLib(): ToretZasilkovnaLib
{
    return new ToretZasilkovnaLib();
}