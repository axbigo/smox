<?
class SOX_Webfront extends SOX_Object  {

	public function Reload()                    { return WFC_Reload($this->id); }
	public function Popup($titel, $text)        { return WFC_SendPopup($this->id, $titel, $text); }
	public function Notify($title, $text)       { return WFC_SendNotification($this->id, $title, $text, '', 0); }
  public function NotifyApp($title, $text)    { return WFC_PushNotification($this->id, $title, $text, '', 0); }

  public function Enable() {
      WFC_UpdateVisibility($this->id, 'root', true);
      WFC_UpdateVisibility($this->id, 'default', false);
      WFC_Reload($this->id);
  }

  public function Disable() {
      WFC_UpdateVisibility($this->id, 'root', false);
      WFC_UpdateVisibility($this->id, 'default', true);
      WFC_Reload($this->id);
  }


}
