<?

class SOX_Display extends SOX_Object  {

	public function setDisplay($text) {
        $id = $this->id;
        @HM_WriteValueString($id, "TEXT", $text);
        @HM_WriteValueString($id, "BEEP", 1);
        @HM_WriteValueString($id, "BACKLIGHT", 1);
        @HM_WriteValueString($id, "UNIT", 0);
        @HM_WriteValueBoolean($id, "SUBMIT", True);
    }
}
