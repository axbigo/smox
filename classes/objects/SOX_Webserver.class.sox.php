<?
class SOX_Webserver extends SOX_Object  {

    public function SetOff()            { IPS_DeleteInstance($this->id); }
}
