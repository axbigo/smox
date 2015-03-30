<?
class SOX_Dimmer extends SOX_Switch  {

	public function SetLevel($value, $speed = 1)
	{
    if ($this->type == SOXConst::MODULE_EIB_GROUP)
    {

      if (EIB_GetGroupInterpretation($this->id) == 'Standard') $value = $value * 2.55; // we have to convert percentage in 0 ... 255
      return EIB_DimValue($this->id, $value);

    }

    if ($this->type == SOXConst::MODULE_HM)
		{
			HM_WriteValueFloat($this->id, "RAMP_TIME", $speed);
			HM_WriteValueFloat($this->id, "LEVEL", $value/100);
			$varid = IPS_GetVariableIDByName("SOX_INTENSITY", $this->id);
			SetValue($varid, $value);
			return;
		}
		if ($this->type == SOXConst::MODULE_FS20) FS20_SetIntensity($this->id, intval($value/6.25), $speed);
	}

	public function GetLevel()	{
        if ($this->type == SOXConst::MODULE_HM)   return GetValue(IPS_GetObjectIDByName('LEVEL', $this->id));
        if ($this->type == SOXConst::MODULE_FS20) return GetValue(IPS_GetObjectIDByName('Intensity', $this->id));
    }

	public function DIMM($value, $speed = 1)    { $this->SetLevel($value, $speed); }
  public function GetValue()                  { return $this->GetLevel(); }
  public function ON($secs = 0)               { parent::ON();}
  public function OFF($secs = 0)              { parent::OFF(); }

  public function getLastChange() {
    if ($this->type == SOXConst::MODULE_HM)    $varId = @IPS_GetObjectIDByName('LEVEL', $this->id);
    if ($this->type == SOXConst::MODULE_FS20)  $varId = @IPS_GetObjectIDByName('Intensity', $this->id);
    $varInfo = IPS_GetVariable($varId);
    return $varInfo['VariableChanged'];
  }

  public function getValueFormatted() {
    return 100 * $this->getLevel() . ' %';
  }

}

