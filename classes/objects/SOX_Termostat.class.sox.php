<?
class SOX_Termostat extends SOX_Object  {

    public function SetTargetTemperature($temp) { if ($this->type == SOXConst::MODULE_FHT) FHT_SetTemperature($this->id, $temp); }
    public function SetMode($mode)              { if ($this->type == SOXConst::MODULE_FHT) FHT_SetMode($this->id, $mode); }
    public function SetModeManual()             { if ($this->type == SOXConst::MODULE_FHT) FHT_SetMode($this->id, 1); }
    public function SetModeAuto()               { if ($this->type == SOXConst::MODULE_FHT) FHT_SetMode($this->id, 0); }

    public function GetTemperature()            { return GetValue(IPS_GetObjectIDByName('Temperature', $this->id)); }
    public function GetTargetTemperature()      { return GetValue(IPS_GetObjectIDByName('Target Temperature', $this->id)); }
    public function GetBattery()                { return GetValue(IPS_GetObjectIDByName('Battery', $this->id)); }
    public function GetPosition()               { return GetValue(IPS_GetObjectIDByName('Position', $this->id)); }

///////////// Aliases & Shortcuts

    public function SetTemp($temp)              { return $this->SetTargetTemperature($temp); }
    public function GetTemp()                   { return $this->GetTemperature(); }
    public function GetTargetTemp()             { return $this->GetTargetTemperature(); }
    public function GetValue()                  { return $this->GetTargetTemperature(); }
    public function GetValueFormatted()         { return $this->GetTargetTemperature() . ' C'; }
    public function GetPos()                    { return $this->GetPosition(); }
    public function GetBatt()                   { return $this->GetBattery(); }

  public function getLastChange() {
    $varId = @IPS_GetObjectIDByName('Target Temperature', $this->id);
    $varInfo = IPS_GetVariable($varId);
    return $varInfo['VariableChanged'];
  }

}
