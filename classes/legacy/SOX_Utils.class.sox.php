<?
class SOX_Utils  {

	public function GetBuild()          { return SOX::GetBuild(); }
	public function TIMESTAMP()         { return SOX::GetTimestamp(); }
	public function VERSION()           { return SOX::GetVersion();	}
	public function LOCATION()          { return SOX::GetLocation();}
	public function GETIP()             { return SOX::GetIp();}
	public function SYSINFO()           { return SOX::GetIpsVersion(); }
	public function Encrypt($string)    { return $string;}
	public function Decrypt($string)    { return $string;}
  public function SwitchMonitorOn()   { return true; }
  public function SwitchMonitorOff()  { return true; }
	public function IPS_VERSION()       { return SOX::GetIpsVersion(); }
  public function GetFreeThreads()    { return SOX::GetFreeThreads(); }
	public function SendTelegramm($qos='N/A', $msg='N/A') { return SOX::SendTelegramm($qos, $msg); }
	public function SetSystemTime()     { return SOX::setSystemTime(); }
  public function getCPU()            { return SOX::GetCPU(); }
  public function getMEM()            { return SOX::GetMEM(); }
  public function getHDD()            { return SOX::GetHDD(); }

  public function XORCrypt($message)
  {
        $encrypted = '';
        $length = strlen($message);
        $password = SOXConst::ENCRIPTION_KEY;
        $pwlen = strlen($password);

        $j = 0;
        for ($i=0; $i < $length; $i++) {
            $encrypted .= (string)($message[$i] ^ $password[$j]);
            $j++;
            if ($j == $pwlen) $j = 0;
        }
        return $encrypted;
    }
///////////////////////////// Backward compatibility aliases

	public function TELEGRAMM($qos, $msg) { SOX::SendTelegramm($qos, $msg); }
}
