<?

class SOX_Logger {

		public function __construct()
		{
            if (!file_exists(SOX_LOG_DB))
            {
                $db = new SQLite3(SOX_LOG_DB);
                $statement = "CREATE TABLE log (ID INTEGER PRIMARY KEY AUTOINCREMENT, TIMESTAMP double, TYPE char, LOCATION char(50), SYSTEM char(50), MESSAGE char(255), REPLICATED int(1))";
                $db->exec($statement);
                $db->close();
            }
		}

		private function write ($type, $location, $system, $message)
		{		
            $ts = microtime(true);
            $tlabel = date('YmdHis', time());
            $msec = 1000000000 * round(fmod($ts, 1), 9);

            $logObject = new SOX_Object("log.$tlabel.$msec");

            $logObject->timestamp = $ts;
            $logObject->type = $type;
            $logObject->location = $location;
            $logObject->system = $system;
            $logObject->message = $message;

//            $logObject->persist();

            $sql_stat = "INSERT INTO log VALUES (NULL, $ts, '$type', '$location', '$system', '$message', 0)";
            $db = new SQLite3(SOX_LOG_DB);
            if (IPS_SemaphoreEnter('SOXLOG', 2000)) { $db->exec($sql_stat); IPS_SemaphoreLeave('SOXLOG'); }
            $db->close();


    }
		
		public function WriteInfo($location, $system, $message)     { $this->write('I', $location, $system, $message); }
		public function WriteAlert($location, $system, $message)    { $this->write('A', $location, $system, $message); }
		public function WriteSystem($location, $system, $message)   { $this->write('S', $location, $system, $message); }
		public function WriteWarning($location, $system, $message)  { $this->write('W', $location, $system, $message); }

		private function Read($type, $lines)
		{
            $list = "";
            $db = new SQLite3(SOX_LOG_DB);
            $results = $db->query("SELECT * FROM log WHERE TYPE LIKE '$type' ORDER BY TIMESTAMP DESC LIMIT $lines");
            while ($row = $results->fetchArray())
                $list = $list.date(("Y-m-d H:i:s"), $row['TIMESTAMP'])." ".$row['TYPE']." ".$row['LOCATION']." ".$row['SYSTEM']." ".$row['MESSAGE']."\n";
            $db->close();
      return $list;
		}

		public function ReadAlert($lines)   { return $this->Read('A', $lines); }
		public function ReadInfo($lines)    { return $this->Read('I', $lines); }
		public function ReadSystem($lines)  { return $this->Read('S', $lines); }
		public function ReadWarning($lines) { return $this->Read('W', $lines); }
		public function ReadAll($lines)     { return $this->Read('%', $lines); }

		public function ReadFiltered($type, $location, $system, $lines)
		{
            $list = "";
            $db = new SQLite3(SOX_LOG_DB);
            $results = $db->query("SELECT * FROM log WHERE TYPE LIKE '$type' AND LOCATION LIKE '$location' and SYSTEM like '$system' ORDER BY TIMESTAMP DESC LIMIT $lines");
            while ($row = $results->fetchArray())
                $list = $list.date(("Y-m-d H:i:s"), $row['TIMESTAMP'])." ".$row['TYPE']." ".$row['LOCATION']." ".$row['SYSTEM']." ".$row['MESSAGE']."\n";
            $db->close();
            return $list;
		}

        public function sendRemote() {

            $link = mysql_connect('sox.upcbiz.ro', 'soxupcbi_remote', '_2UCCHx7+@@K');
            if (!$link) {
                $this->WriteWarning('SYSTEM', 'LOG', 'Could not connect to remote logging server!');
                return false;
            }

            $db_selected = mysql_select_db('soxupcbi_sencontrol', $link);
            if (!$db_selected) {
                $this->WriteWarning('SYSTEM', 'LOG', 'Could not select remote logging database!');
                return false;
            }

            $result = array();
            $db = new SQLite3(SOX_LOG_DB);
            $results = $db->query("SELECT * FROM log WHERE REPLICATED = 0");

            while ($row = $results->fetchArray(SQLITE3_ASSOC))
            {
                $row['SYSNAME'] = SOX_LOCATION;
                $result[] = $row;
                $db->query("UPDATE log SET REPLICATED = 1 WHERE ID = " . $row['ID']);
                $ts = date('Y-m-d H:i:s', time() + SOX_TIME_OFFSET * 3600);

                $sysnameRow = $row['SYSNAME'];
                $typeRow = $row['TYPE'];
                $systemRow = $row['SYSTEM'];
                $locationRow = $row['LOCATION'];
                $messageRow = $row['MESSAGE'];

                $sql_stat = "INSERT INTO LOG (SYSNAME, TIMESTAMP, TYPE, LOCATION, SYSTEM, MESSAGE) VALUES (
                    '$sysnameRow',
                    '$ts',
                    '$typeRow',
                    '$systemRow',
                    '$locationRow',
                    '$messageRow')";

                $result = mysql_query($sql_stat);
                if ($result) {
                    $this->WriteWarning('SYSTEM', 'LOG', 'Could not execute remote logging query!');
                    return false;
                }
            }

            mysql_close($link);
            return true;
        }

/*********************** Aliases for backward compatibility *********************/

		public function Read_All($lines)      {return $this->ReadAll($lines);}
		public function Read_Info($lines)     {return $this->ReadInfo($lines);}
		public function Read_System($lines)   {return $this->ReadSystem($lines);}
		public function Read_Warning($lines)  {return $this->ReadWarning($lines);}

		public function Write_Info($location, $system, $message)    { $this->WriteInfo($location, $system, $message); }
		public function Write_Warning($location, $system, $message) { $this->WriteWarning($location, $system, $message); }
		public function Write_Alert($location, $system, $message)   { $this->WriteAlert($location, $system, $message); }
		public function Write_System($location, $system, $message)  { $this->WriteSystem($location, $system, $message); }

}

