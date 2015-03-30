<?

class SOX_Shutter extends SOX_Object  {

  public function MoveUp() { return EIB_DriveMove($this->id, false); }
  public function MoveDown() { return EIB_DriveMove($this->id, true); }

  public function StepUp() { return EIB_DriveStep($this->id, false); }
  public function StepDown() { return EIB_DriveStep($this->id, true); }

}

