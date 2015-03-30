<?
class SOX_Event extends SOX_Object  {

    public function SetActive($state)   { IPS_SetEventActive($this->GetId(), $state); }
    public function SetOn()             { IPS_SetEventActive($this->GetId(), true); }
    public function SetOff()            { IPS_SetEventActive($this->GetId(), false); }
    public function GetInfo()           { return IPS_GetEvent ($this->GetId); }
    public function IsActive()          { $resInfo = IPS_GetEvent ($this->GetId()); return $resInfo['EventActive']; }

    public function SetEventCyclicDateBounds($start, $stop) {
        IPS_SetEventCyclicDateBounds($this->GetId(), $start, $stop);
    }

    public function SetEventCyclicTimeBounds($start, $stop) {
        IPS_SetEventCyclicTimeBounds($this->GetId(), $start, $stop);
    }
}
