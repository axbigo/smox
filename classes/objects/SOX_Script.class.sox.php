<?
class SOX_Script extends SOX_Object {

  private function convertArgs($args = array()) {
    $soxArgs = array();
    foreach($args as $key => $value) { $soxArgs['SOX_' . $key] = $value; }
    return $soxArgs;
  }

  public function Run()                       { return IPS_RunScript($this->id); }
  public function RunWait()                   { return IPS_RunScriptWait($this->id); }
  public function RunEx($args = array())      { return IPS_RunScriptEx($this->id, $this->convertArgs($args)); }
  public function RunExWait($args = array())  { return IPS_RunScriptExWait($this->id, $this->convertArgs($args)); }
}

