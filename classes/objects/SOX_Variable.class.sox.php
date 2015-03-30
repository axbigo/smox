<?
class SOX_Variable extends SOX_Object {

    public function SetValue($val)  { return SetValue($this->id, $val); }

    public function GetValue()      { return GetValue($this->id); }

    public function GetValueFormatted()      {
      $varInfo = IPS_GetVariable($this->id);
      if ($varInfo['VariableCustomProfile'])  return GetValueFormatted($this->id);
        else return GetValue($this->id);
    }

    public function getLastChange() {
        $varInfo = IPS_GetVariable($this->id);
        $ts = $varInfo['VariableChanged'];
        return $ts;
    }
}
