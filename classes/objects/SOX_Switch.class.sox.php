<?

class SOX_Switch extends SOX_Object  {

  public function __construct($idn) { parent::__construct($idn); }

  public function ON($secs = 0)
	{
    if ($this->type == SOXConst::MODULE_EIB_GROUP) { EIB_Switch($this->id, true); return; }

    if ($this->type == SOXConst::MODULE_HM   && $secs == 0) { @HM_WriteValueBoolean($this->id, "STATE", true); return; }
		if ($this->type == SOXConst::MODULE_HM   && $secs != 0)
		{
            @HM_WriteValueFloat($this->id, "ON_TIME", $secs);
            @HM_WriteValueBoolean($this->id, "STATE", true);
		}

    if ($this->type == SOXConst::MODULE_FS20 && $secs == 0) { IPS_Sleep(100); FS20_SwitchMode($this->id, true); return; }
    if ($this->type == SOXConst::MODULE_FS20 && $secs != 0) { IPS_Sleep(100); FS20_SwitchDuration($this->id, true, $secs); return; }
  }

	public function OFF($secs = 0)
	{
    if ($this->type == SOXConst::MODULE_EIB_GROUP) { EIB_Switch($this->id, false); return; }

    if ($this->type == SOXConst::MODULE_HM   && $secs == 0) { @HM_WriteValueBoolean($this->id, "STATE", false); return; }
		if ($this->type == SOXConst::MODULE_HM   && $secs != 0)
		{
            @HM_WriteValueFloat($this->id, "ON_TIME", $secs);
            @HM_WriteValueBoolean($this->id, "STATE", false);
		}

    if ($this->type == SOXConst::MODULE_FS20 && $secs == 0) { IPS_Sleep(100); FS20_SwitchMode($this->id, false); return; }
    if ($this->type == SOXConst::MODULE_FS20 && $secs != 0) { IPS_Sleep(100); FS20_SwitchDuration($this->id, false, $secs); return; }
  }

  public function GetState()
  {
    if ($this->type == SOXConst::MODULE_HM)   return GetValue(IPS_GetObjectIDByName('STATE',  $this->id));
    if ($this->type == SOXConst::MODULE_HMS)  return GetValue(IPS_GetObjectIDByName('Status', $this->id));
    if ($this->type == SOXConst::MODULE_FS20) return GetValue(IPS_GetObjectIDByName('Status', $this->id));
  }


/////////////////////// Aliases

    public function GetValue() { return $this->GetState(); }
    public function GetValueFormatted() { return $this->GetState() ? 'ON' : 'OFF'; }

    public function getLastChange() {
      if ($this->type == SOXConst::MODULE_HMS)   $varId = @IPS_GetObjectIDByName('Status', $this->id);
      if ($this->type == SOXConst::MODULE_HM)    $varId = @IPS_GetObjectIDByName('STATE', $this->id);
      if ($this->type == SOXConst::MODULE_FS20)  $varId = @IPS_GetObjectIDByName('Status', $this->id);
      $varInfo = IPS_GetVariable($varId);
      return $varInfo['VariableChanged'];
    }

}

