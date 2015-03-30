<?
class SOX_Telegramm {

  public $senderId;
  public $senderTs;
  public $receiverId;
  public $receiverTs;
  public $key;
  public $type;
  public $status;
  public $message;
  public $execTime;
  public $payload;


  public function __construct($payload) {

    $this->senderTs = SOX::gmtTimeStamp();
    $this->senderId = SOX::GetLocation();
    $this->key = sha1($this->senderTs . SOXConst::TELEGRAMM_KEY . $this->senderId . $payload);
    $this->status = SOXConst::TYPE_TLG_NEW;
    $this->message = null;
    $this->execTime = 0;

    $this->payload = base64_encode(SOX::XORCrypt($this->key, $payload));

  }

  // Todo

  public function sendTo($ip) {
    return false;
  }

  public function getResponse($ip) {
    return false;
  }

}
