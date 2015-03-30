<?
abstract class SOX   {





  //************************************************** UTILS **************************************

  public static function GetBuild()             { return SOX_BUILD; }
  public static function GetTimestamp()         { return date("Y-m-d H:i:s"); }
  public static function GetVersion()           { return SOXConst::VERSION;	}
  public static function GetLocation()          { return SOX_LOCATION;}
  public static function GetIp()                { return file_get_contents(SOX_CONTROL.'/SOX_GetIP.php');}
  public static function GetSysInfo()           { return  'LOCATION: '.SOX_LOCATION.' / IPS: ' . SOX::GetIpsVersion() .  ' / SOX: '.SOX::GetVersion();  }

  public static function GetIpsVersion()
  {
    $IPSVer = IPS_GetLiveUpdateVersion();
    $len = strlen($IPSVer);
    $IPSNum = substr($IPSVer, $len - 4, 4);
    return IPS_GetKernelVersion()." #$IPSNum";
  }

  public static function GetFreeThreads()
  {
    $totalCount =  count(IPS_GetScriptThreadList());
    $usedCount = 0;
    for ( $i = 1; $i < $totalCount; $i++ ) { $threadInfo = (IPS_GetScriptThread($i)); if ($threadInfo['FilePath'] || $threadInfo['ScriptID']) $usedCount++; }
    return 100 * $usedCount/$totalCount;
  }

  public static function SendTelegramm($qos='N/A', $msg='N/A')
  {
    $LOC = SOX::GetLocation();
    $SOXVER = SOX::GetVersion().'.'.SOX::GetBuild();
    $THR = SOX::GetFreeThreads();
    $IPS = IPS_GetLiveUpdateVersion();
    $TS  = microtime(true);
    $CPU = SOX::GetCPU();
    $MEM = SOX::GetMEM();
    $HDD = SOX::GetHDD();
    $BLD = '';
    $UPT = date("Y-m-d H:i:s", IPS_GetUptime());

    $remoteCode = "
	    \$ip = \$_SERVER['REMOTE_ADDR']; echo updateMaster('$SOXVER', '$IPS', '$HDD', '$CPU', '$MEM', \$ip, '$qos', '$msg', '$BLD', '$UPT', '$LOC' );
    ";

    return SOX::executeRemote(SOX_CONTROL, $remoteCode);
  }

  public static function GetCPU() { $cpu = Sys_GetCPUInfo(); return $cpu['CPU_AVG']; }
  public static function GetMEM() { $memory = Sys_GetMemoryInfo();return intval($memory['AVAILPHYSICAL']/1000000); }
  public static function GetHDD() { $hddinfo = Sys_GetHarddiskInfo(); return intval($hddinfo['HDD0']['FREE']/1000000); }

  public static function XORCrypt($password = false, $message)
  {
    $encrypted = '';
    $length = strlen($message);
    if (!$password) $password = SOXConst::ENCRIPTION_KEY;
    $pwlen = strlen($password);

    $j = 0;
    for ($i=0; $i < $length; $i++) {
      $encrypted .= (string)($message[$i] ^ $password[$j]);
      $j++;
      if ($j == $pwlen) $j = 0;
    }
    return $encrypted;
  }

  //************************************************** END UTILS **************************************


  /**
   * Send a POST requst using cURL
   * @param string $url to request
   * @param array $post values to send
   * @param array $options for cURL
   * @return string
   */
  private static function curl_post($url, array $post = array(), array $options = array())
  {
    $defaults = array(
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_URL => $url,
      CURLOPT_FRESH_CONNECT => 1,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_FORBID_REUSE => 1,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
      trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
  }

  public static function gmtTimeStamp($time = '')
  {
    if ($time == '')
      $time = time();
    return mktime( gmdate("H", $time), gmdate("i", $time), gmdate("s", $time), gmdate("m", $time), gmdate("d", $time), gmdate("Y", $time));
  }


  public static function executeRemote($url, $code)
  {
    $startTime = microtime(true);
    $tg = new SOX_Telegramm($code);
    $tg->type = SOXConst::TYPE_TLG_EXEC;

    $objVars = get_object_vars($tg);

    $returnTlg = json_decode(base64_decode(SOX::curl_post($url . '/soxremotecurl.php', $objVars)));
    if (!$returnTlg) return false;

    $returnTlg->payload = SOX::XORCrypt($returnTlg->key, base64_decode($returnTlg->payload));
    foreach ($objVars as $varName => $value) $tg->$varName = $returnTlg->$varName;

    $tg->execTime = (microtime(true) - $startTime);
    return $tg;
  }


  //************************************************** BACKUP **************************************




  private static function DELETEFILES($directory, $seconds_old)
  {
    $count = 0;

    if( !$dirhandle = @opendir($directory)) return;

    while( false !== ($filename = readdir($dirhandle)) ) {
      if( $filename != "." && $filename != ".." ) {
        $filename = $directory. "/". $filename;
        if( @filemtime($filename) < (time()-$seconds_old) )
        {
          @unlink($filename);
          $count++;
        }
      }
    }
    return $count;
  }

  private static function CLEANUP()
  {
    $Log = new SOX_Logger();

    $days = SOX_CLEANUP_DAYS;
    $seconds = 24 * 60 * 60 * $days;

    $back = SOX::DELETEFILES( SOX_BACKUP_PATH, $seconds);

    $db = new SQLite3(SOX_LOG_DB);
    $oldts = microtime(true)-$seconds;
    $statement = 'DELETE FROM log WHERE TIMESTAMP <= '. $oldts;
    $db->exec($statement);
    $db->close();

    $msg = "Deleted: ".$back." backup files";
    $Log->Write_System('SYSTEM', 'CLEANUP', $msg);
  }

  public static function Backup()
  {
    ini_set('max_execution_time', 300);

    $Log = new SOX_Logger();

    $Log->Write_System('SYSTEM', 'BACKUP', "Backup process started");

    // Create unique backup file name

    $fname = sprintf("%s-%s.zip", SOX::GetLocation(), date("Y-m-d-H-i-s"));
    $fname = str_replace(' ', '_', $fname);
    $fpath = SOX_BACKUP_PATH.'\\'.$fname;

    // Start zipping

    $success = true;

    $zip = new ZipArchive();

    if ($zip->open($fpath, ZIPARCHIVE::CREATE))
    {
      $iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(IPS_GetKernelDir().'sox'));
      foreach ($iterator as $key=>$value) {
        $fm = basename($key);
        if ($fm !=  '.' && $fm != '..') $succes = $success && $zip->addFile($key, $fm) or die ("ERROR: Could not add file: $key");
      }

      $iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(IPS_GetKernelDir().'scripts'));

      foreach ($iterator as $key=>$value) {
        $fm = basename($key);
        if ($fm !=  '.' && $fm != '..') $succes = $success && $zip->addFile($key, $fm) or die ("ERROR: Could not add file: $key");
      }

      //    echo $zip->addFile(IPS_GetKernelDir().'\db\logging.db', 'logging.db') or die ("ERROR: Could not add file: logging.db");
      $success = $success && $zip->addFile(IPS_GetKernelDir().'settings.json', 'settings.json') or die ("ERROR: Could not add file: settings.json");

      $success = $success && $zip->close();

      if ($success)
      {
        $Log->Write_System('SYSTEM', 'BACKUP', "Backup process finished successfully");
        SOX::sendChatMessage("Backup process finished successfully at " . date('Y-m-d H:i:s', SOX::gmtTimeStamp()) . ' GMT');
      }
      else
      {
        $Log->Write_System('SYSTEM', 'BACKUP', "Backup process failed");
        SOX::SendAlert('Backup failed');
      }


      $Log->Write_System('SYSTEM', 'BACKUP', "Upload process started");

      if (SOXBase::doFileUpload($fname, $fpath)) {
        $Log->Write_System('SYSTEM', 'BACKUP', "Upload process finished successfully");
        SOX::sendChatMessage("Upload process finished successfully at " . date('Y-m-d H:i:s', SOX::gmtTimeStamp()) . ' GMT');
      }

    else {
        $Log->Write_System('SYSTEM', 'BACKUP', "Upload process crashed");
        SOX::SendAlert('Upload failed');
      }
    }
    SOX::CLEANUP();
  }

// ********************************************* END BACKUP *********************************************


// ********************************************* ALERTER *********************************************

  private static function doSendMail($mailto, $head, $msg)
  {
    global $SOX_MAILER;
    return $SOX_MAILER->SendMail($mailto, $head, $msg);
  }

  private static function SendPushApp($title, $text)
  {
      global $SOX_WEBFRONT;
      $SOX_WEBFRONT->NotifyApp($title, $text);
  }

  private static function initJaxlService($jid, $pass)
  {

  }

  public static function sendChatMessage($message, $receiver = SOX_CHAT_RECEIVER)
  {
    global $soxJaxlClient;

    require_once IPS_GetKernelDir().'\\sox\\vendors\\JAXL\\jaxl.php';

    $soxJaxlClient = new JAXL(array(
      'jid' => SOX_CHAT_ID,
      'pass' => SOX_CHAT_PWD,
      'host' => 'jabber.1und1.de',
      'port' => 5222
    ));

    $soxJaxlClient->receiverJid = $receiver;
    $soxJaxlClient->message = $message;

    //
    // required XEP's
    //
    $soxJaxlClient->require_xep(array(
      '0199'	// XMPP Ping
    ));

    //
    // add necessary event callbacks here
    //

    $soxJaxlClient->add_cb('on_auth_success', function() {
      global $soxJaxlClient, $jid, $msg;

      // fetch roster list
  //    $soxJaxlClient->get_roster();

      // fetch vcard
//      $soxJaxlClient->get_vcard();

      // set status
      $soxJaxlClient->set_status("Last known activity " . date('Y-m-d H:i:s'), "", 10);
      $soxJaxlClient->send_chat_msg($jid, $msg);
      $soxJaxlClient->send_chat_msg($soxJaxlClient->receiverJid, $soxJaxlClient->message);
      $soxJaxlClient->send_end_stream(); 


    });

    // by default JAXL instance catches incoming roster list results and updates
    // roster list is parsed/cached and an event 'on_roster_update' is emitted
    $soxJaxlClient->add_cb('on_roster_update', function() {
      //global $soxJaxlClient;
      //print_r($soxJaxlClient->roster);
    });

    $soxJaxlClient->add_cb('on_auth_failure', function($reason) {
      global $soxJaxlClient;
      _info("got on_auth_failure cb with reason $reason");
      $soxJaxlClient->send_end_stream();
    });

    $soxJaxlClient->add_cb('on_chat_message', function($stanza) {
      global $soxJaxlClient;

      // echo back incoming chat message stanza
      $stanza->to = $stanza->from;
      $stanza->from = $soxJaxlClient->full_jid->to_string();
      //$soxJaxlClient->send($stanza);

    });


    $soxJaxlClient->add_cb('on_disconnect', function() {
      _info("got on_disconnect cb");
    });

    //
    // finally start configured xmpp stream
    //

    $soxJaxlClient->start();
  }

  public static function SendSMS($text, $empfanger = '')
  {
    $Log = new SOX_Logger();
    $text = rawurlencode(substr($text, 0, 160));
    $url = "http://www.innosend.de/gateway/sms.php?id=senzoria&pw=Sen,123&text=$text&type=12&empfaenger=$empfanger";
    fopen($url, 'r');
    $Log->WriteInfo('SYSTEM', 'SMS', 'Message was sent to ' . $empfanger);
  }

  public static function SendMail($text, $sendTo = SOXConst::DEFAULT_MAIL)
  {
    $ts = SOX::GetTimestamp();
    @SOX::doSendMail($sendTo, "[$ts] SENZORIA " . SOX_SYSNAME, $text);
  }

  public static function SendAlert($text, $smsTo = SOXConst::DEFAULT_SMS, $mailTo = SOXConst::DEFAULT_MAIL)
  {
    $Log = new SOX_Logger();

    $ts = SOX::GetTimestamp();

    if (@constant('SOX_ALERT_SMS'))  SOX::SendSMS("[$ts] SENZORIA ALERT: " . SOX_SYSNAME . " " . $text, $smsTo);
    if (@constant('SOX_ALERT_MAIL')) SOX::SendMail('SENZORIA ALERT: '.$text, $mailTo);
    if (@constant('SOX_ALERT_PUSH')) SOX::SendPushApp('SENZORIA ALERT:', $text);
    if (@constant('SOX_ALERT_CHAT')) SOX::sendChatMessage(SOX_CHAT_RECEIVER, $text);

    $Log->WriteAlert('SYSTEM', 'ALERT', $text);
  }

  // ********************************************* END ALERTER *********************************************

  // ********************************************* ASTRO *********************************************


  public static function AstroGetSunrise()    { return date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, SOX_GPSY, SOX_GPSX, 96, SOX_OFFSET) - 30*60; }
  public static function AstroGetSunset()     { return date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, SOX_GPSY, SOX_GPSX, 96, SOX_OFFSET) - 30*60; }
  public static function AstroIsSummer()      { if (date("I", time())) return true; return false; }
  public static function AstroIsDST()         { if (date("I", time())) return true; return false; }
  public static function AstroGetKW()         { return date("W"); }


  // ********************************************* END ASTRO *********************************************


  // SOX Ping

  public static function checkConnection($host, $port=80, $timeout=1) {
    $tB = microtime(true);
    $fP = @fSockOpen($host, $port, $errno, $errstr, $timeout);
    if (!$fP) { return false; }
    $tA = microtime(true);
    return round((($tA - $tB) * 1000), 0);
  }

  public static function checkSpeed() {
    $ts = microtime(true);
    $file = file_get_contents('http://senzoria.com/control/random');
    if (!$file) return 0;
    $seconds = microtime(true) - $ts;
    $size = strlen($file) * 8;
    $speed = $size / $seconds;
    return $speed / 1000000;

  }

  public static function setSystemTime()
  {
    return shell_exec('W32tm /resync');
  }

  public static function restartSystem()
  {
    return shell_exec('c:\windows\system32\shutdown.exe /r /t 15 /c "Standard system restart in 15 seconds."');
  }


  public static function GetObjects()
  {
    $allObjects = IPS_GetObjectList();
    $objectList = array();

    foreach ($allObjects as $myObjectId) {
      $objName = IPS_GetName($myObjectId);
      if (substr($objName, 0, 1) == '$') $objectList[] = $objName;
    }

    return $objectList;
  }

  public static function BuildApp()
  {

    echo "Start building application ...\n";
    echo "Checking name conflicts...\n";

    $allObjects = IPS_GetObjectList();
    $objectList = array();

    foreach ($allObjects as $myObjectId) {
      $objName = IPS_GetName($myObjectId);
      if (substr($objName, 0, 1) == '$') $objectList[] = $objName;
    }

    $duplicateNames = array_unique( array_diff_assoc( $objectList, array_unique( $objectList ) ) );
    if (count($duplicateNames) > 0) {
      foreach($duplicateNames as $dupName)
      {
        echo "     Duplicate name $dupName on ID's: ";
        foreach ($allObjects as $myObjectId) {
          $objName = IPS_GetName($myObjectId);
          if ($dupName == $objName) echo "[$myObjectId] ";
        }
        echo "\n";
      }
      echo "Aborting process. Please rename the conflictive objects then try again!\n";
      return;
    }
    else echo "No name conflicts found!\n";

    $app = '';
    $indexData = '';
    $indexArray = array();
    $objectsCount = 0;

    $app .= "const SOX_APP_BUILD = '" . SOX::GetTimestamp() . "';\n";

    $config = file_get_contents(IPS_GetKernelDir() . "sox\\config.php");
    $allObjects = IPS_GetObjectList();

    $mailers = array();
    $webFronts = array();

    foreach ($allObjects as $myObjectId) {
      $objName = IPS_GetName($myObjectId);
      if (substr($objName, 0, 1) == '$') {
        $newObj = SOXBase::GenerateObjectById($myObjectId);
        $serObj = base64_encode(serialize($newObj));
        $indexData .= $objName . " = unserialize(base64_decode('". $serObj . "'));\n";
        $indexArray[$myObjectId] = $objName;
        $indexArray[$objName] = $myObjectId;
        $objectsCount++;

      }
    }


    $app .= $config ."\n\n// *********************************************************************************************************\n\n\n";
    $indexArray = base64_encode(serialize($indexArray));
    $app .= "$". "_SOXINDEX = unserialize(base64_decode('" .$indexArray . "'));\n";
    $app .= $indexData;

    $writeFile = fopen(IPS_GetKernelDir() . "sox\\sox_app.php", 'w');
    fwrite($writeFile, '<');
    fwrite($writeFile, '?');
    fwrite($writeFile, "\n");
    fwrite($writeFile, $app);
    fclose($writeFile);

    $nrObj = count($allObjects);
    $perCent = round((100 * $objectsCount / $nrObj), 2);

    echo "Finished building App. \nSOX-ification degree: $perCent %\n";

    SOX::sendChatMessage("App built at " . SOX::gmtTimeStamp() . ' GMT. Soxification degree: ' . $perCent .' %');
  }

    public static function logChanges($objects = array(), $timeDiff = 0)
    {
        $logger = new SOX_Logger();
        foreach ($objects as $object)
        {
            $ts = $object->getLastChange();
            if ($ts >= time() - $timeDiff) {
                $msg = IPS_GetLocation($object->id) . " changed to " . $object->GetValueFormatted();
                $logger->WriteInfo('SYSTEM', 'VAR', $msg);
            }
        }
    }

}
