<?
class SOX_Camera extends SOX_Object {


    public $ip;
    public $api;
    public $type;
    public $url;
    private $userName;
    private $userPassword;

    public function __construct($idn) {

        parent::__construct($idn);

        $idvar = @IPS_GetObjectIDByName('CAMERA', $this->id);
        if ($idvar > 0) $this->api = GetValue($idvar);

        $idvar = @IPS_GetObjectIDByName('IP', $this->id);
        if ($idvar > 0) $this->ip = GetValue($idvar);

        $idvar = @IPS_GetObjectIDByName('USER_NAME', $this->id);
        if ($idvar > 0) $this->userName = GetValue($idvar);

        $idvar = @IPS_GetObjectIDByName('USER_PASSWORD', $this->id);
        if ($idvar > 0) $this->userPassword = GetValue($idvar);


        if ($this->api == 'Vivotek' || $this->api == 'Vivotek8') {
            $this->url = "http://" . $this->userName . ":" . $this->userPassword . "@" . $this->ip . "/cgi-bin/";
            $this->type = $this->getType();
        }
        if ($this->api == 'Foscam') {
            $this->url = "http://".$this->ip;
            $this->type = 'Foscam (generic)';
        }

    }

    public function reset($seconds = 0) {
        $userName = $this->userName;
        $userPassword = $this->userPassword;

        if ($this->api == 'Vivotek' || $this->api == 'Vivotek8')
            return @Sys_GetUrlContent($this->url."admin/setparam.cgi?system_reset=$seconds");

        if ($this->api == 'Foscam')
            return @Sys_GetUrlContent($this->url . "/reboot.cgi?user=$userName&pwd=$userPassword&next_url=");

        return false;


    }

    public function config($param, $value) {

        if ($this->api == 'Vivotek' || $this->api == 'Vivotek8') {
            $url = $this->url."admin/setparam.cgi?$param=$value";
            return Sys_GetUrlContent($url);
        }

        return false;
    }

    public function mute($mute) {
        if ($this->api != 'Vivotek') return false;

        if ($mute) $url = $this->url."admin/setparam.cgi?audioin_c<0_1";
        return Sys_GetUrlContent($url);
    }

    public function isAlive($mSec = 1000) {
        return Sys_Ping($this->ip, $mSec);
    }

    public function getType() {
        if ($this->api == 'Foscam') return 'Foscam (generic)';
        if (!$this->isAlive()) return ('N/A');

        if ($this->api == 'Vivotek' || $this->api == 'Vivotek8') {
            $url = $this->url."admin/getparam.cgi?system";
            $sysInfo = Sys_GetUrlContent($url);
            $pos1 = strpos($sysInfo, 'system_info_modelname') + 23;
            $pos2 = strpos($sysInfo, 'system_info_extendedmodelname');
            return substr($sysInfo, $pos1, ($pos2 - $pos1) - 3);
        }

        return false;
    }

    private function step($direction, $degree) {
        $userName = $this->userName;
        $userPassword = $this->userPassword;

        $url = $this->url . "/decoder_control.cgi?user=$userName&pwd=$userPassword&command=$direction&onestep=1&degree=$degree";

        return Sys_GetUrlContent($url);

    }

    public function stepLeft($degree='') {
       if ($this->api != 'Foscam') return false; // Only for Foscam
       $this->step(6, $degree);
       return true;
    }

    public function stepRight($degree='') {
        if ($this->api != 'Foscam') return false; // Only for Foscam
        $this->step(4, $degree);
        return true;
    }

    public function stepUp($degree='') {
        if ($this->api != 'Foscam') return false; // Only for Foscam
        $this->step(0, $degree);
        return true;
    }

    public function stepDown($degree='') {
        if ($this->api != 'Foscam') return false; // Only for Foscam
        $this->step(2, $degree);
        return true;
    }

    public function setMotionDetection($on) {
        $userName = $this->userName;
        $userPassword = $this->userPassword;

        $onVal = intval($on);

        if ($this->api == 'Foscam') {
            $url = $this->url . "/set_alarm.cgi?user=$userName&pwd=$userPassword&motion_armed=$onVal";
            return Sys_GetUrlContent($url);
        }

        return false;

    }

    public function setSoundDetection($on) {
        $userName = $this->userName;
        $userPassword = $this->userPassword;

        $onVal = intval($on);

        if ($this->api == 'Foscam') {
            $url = $this->url . "/set_alarm.cgi?user=$userName&pwd=$userPassword&sounddtetect_armed=$onVal";
            return Sys_GetUrlContent($url);
        }

         return false;
    }

    public function setStream($stream) {

        $idvar = @IPS_GetObjectIDByName('STREAM', $this->id);
        if ($idvar > 0) {
            SetValue($idvar, $stream);
            return true;
        }
        else return false;
    }

    public function getIp() {
        return $this->ip;
    }


    public function enableEvent($eventId) {
        if ($this->api == 'Foscam') return false;
        else {
           $url = $this->url . "admin/setparam.cgi?event_i" . $eventId . "_enable=1";
           return @SYS_GetUrlContent($url);
        }
    }

    public function disableEvent($eventId) {
        if ($this->api == 'Foscam') return false;
        else {
            $url = $this->url . "admin/setparam.cgi?event_i" . $eventId . "_enable=0";
            return @SYS_GetUrlContent($url);
        }
    }

    public function getSyslog() {
        if ($this->api == 'Foscam') return false;
        else {
            $url = $this->url . "/admin/syslog.cgi";
            return @SYS_GetUrlContent($url);
        }
    }
}

