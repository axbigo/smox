<?

class SOX_Mailer extends SOX_Object  {

  public function SendMail($mailto, $head, $msg)
  {
    if (SOX::checkConnection('smtp.1und1.de', 25, 1)) {        // todo: rewrite with GetProperty to read the smtp address
    return SMTP_SendMailEx($this->id, $mailto , $head , $msg);
  }
    else return false;
  }

}

