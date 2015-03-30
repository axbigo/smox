<?
abstract class SOXBase
{
    public static function GetModule($id) { $instance = @IPS_GetInstance($id); return $instance['ModuleInfo']['ModuleID']; }

    public static function ObjectType($id)
    {
        if (IPS_VariableExists($id))                    return SOXConst::TYPE_VARIABLE;
        if (IPS_ScriptExists($id))                       return SOXConst::TYPE_SCRIPT;
        if (IPS_EventExists($id))                       return SOXConst::TYPE_EVENT;

        $module_id = SOXBase::GetModule($id);

        if ($module_id == SOXConst::MODULE_EIB_GROUP) {

            $EIB_Function = EIB_GetGroupFunction($id);

            switch($EIB_Function) {
                case 'Switch':    return SOXConst::TYPE_SWITCH;
                case 'DimValue':  return SOXConst::TYPE_DIMMER;
                default:          return SOXConst::TYPE_NIL;
            }
        }

        if ($module_id == SOXConst::MODULE_EIB_SHUTTER)       return SOXConst::TYPE_SHUTTER;
        if ($module_id == SOXConst::MODULE_PROJET_C)          return SOXConst::TYPE_COUNTER;
        if ($module_id == SOXConst::MODULE_FS20)              return SOXConst::TYPE_DIMMER;
        if ($module_id == SOXConst::MODULE_HMS)               return SOXConst::TYPE_DETECTOR;
        if ($module_id == SOXConst::MODULE_FHT)               return SOXConst::TYPE_TERMOSTAT;
        if ($module_id == SOXConst::MODULE_WF)                return SOXConst::TYPE_WEBFRONT;
        if ($module_id == SOXConst::MODULE_WEBSERVER)         return SOXConst::TYPE_WEBSERVER;
        if ($module_id == SOXConst::MODULE_DUMMY)             return SOXConst::TYPE_CAMERA;
        if ($module_id == SOXConst::MODULE_SMTP_MAILER)       return SOXConst::TYPE_MAILER;
        if ($module_id == SOXConst::MODULE_MEDIAPLAYER)       return SOXConst::TYPE_PLAYER;
        if ($module_id == SOXConst::MODULE_HM)
        {
            if (@IPS_GetObjectIDByName('LEVEL', $id))      return SOXConst::TYPE_DIMMER;
            if (@IPS_GetObjectIDByName('WORKING', $id))    return SOXConst::TYPE_SWITCH;
            if (@IPS_GetObjectIDByName('STATE', $id))      return SOXConst::TYPE_DETECTOR;

            return SOXConst::TYPE_DISPLAY;
        }

        return SOXConst::TYPE_NIL;
    }


    public static function GenerateObjectById($id)
    {
        $type = SOXBase::ObjectType($id);

        if ($type == SOXConst::TYPE_SWITCH)       return new SOX_Switch($id);
        if ($type == SOXConst::TYPE_DIMMER)       return new SOX_Dimmer($id);
        if ($type == SOXConst::TYPE_VARIABLE)     return new SOX_Variable($id);
        if ($type == SOXConst::TYPE_DETECTOR)     return new SOX_Detector($id);
        if ($type == SOXConst::TYPE_TERMOSTAT)    return new SOX_Termostat($id);
        if ($type == SOXConst::TYPE_EVENT)        return new SOX_Event($id);
        if ($type == SOXConst::TYPE_WEBFRONT)     return new SOX_Webfront($id);
        if ($type == SOXConst::TYPE_DISPLAY)      return new SOX_Display($id);
        if ($type == SOXConst::TYPE_CAMERA)       return new SOX_Camera($id);
        if ($type == SOXConst::TYPE_WEBSERVER)    return new SOX_Webserver($id);
        if ($type == SOXConst::TYPE_SHUTTER)      return new SOX_Shutter($id);
        if ($type == SOXConst::TYPE_MAILER)       return new SOX_Mailer($id);
        if ($type == SOXConst::TYPE_SCRIPT)       return new SOX_Script($id);
        if ($type == SOXConst::TYPE_PLAYER)       return new SOX_Player($id);

        return new SOX_Object($id);
    }


    public static function doFileUpload($fname, $fpath) {
    $content = base64_encode(file_get_contents($fpath));
    $code = "file_put_contents('../backup/$fname', base64_decode('$content'));";
    $res = SOX::executeRemote(SOX_CONTROL, $code);
    if ($res->message == 'Success') return true;
      else return false;
  }



  public static function listDir($dirPath) {

    $returnList = array();

    if( !$dirHandle = @opendir($dirPath)) return;

    while( false !== ($filename = readdir($dirHandle)) ) {
      if( $filename != "." && $filename != ".." ) {
        $filename = $dirPath. '\\' . $filename;
        $returnList[] = $filename;
      }
    }

    return $returnList;
  }

    public static function GetParameterByName($name = '') { return $_IPS['SOX_'.$name]; }

}
