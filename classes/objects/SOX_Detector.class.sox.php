<?

class SOX_Detector extends SOX_Switch {

  private function getStatusVarId() {

    $varId = false;

    $varId = @IPS_GetVariableIDByName("STATE", $this->id);
    if (!$varId) {
      $varId = @IPS_GetVariableIDByName("Status", $this->id);
    }
    if (!$varId) {
      $varId = @IPS_GetVariableIDByName("MOTION", $this->id);
    }
    return $varId;
  }

  public function getState() {
    return getValue($this->getStatusVarId());
  }

  public function getStateFormatted() {
    return getValueFormatted($this->getStatusVarId());
  }
}

