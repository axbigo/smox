<?
class SOX_Alerter {

    public function SendSMS($text, $empfaenger = '')
    {
      SOX::SendSMS($text, $empfaenger);
 		}

    public function SendMail($text, $sendTo = SOXConst::DEFAULT_MAIL)
    {
        @SOX::SendMail($sendTo, "SENZORIA ".SOX_SYSNAME, $text);
    }

    public function SendAlert($text, $smsTo = SOXConst::DEFAULT_SMS, $mailTo = SOXConst::DEFAULT_MAIL)
    {
      SOX::SendAlert($text, $smsTo, $mailTo);
    }
}
