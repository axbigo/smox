<?

class SOX_Object  {

  public $label;
  public $id;
  public $type;
  public $isVerbose = false;

  public function __construct($idn  = 'SOX') {
      $this->type = SOXBase::GetModule($idn);
      if (is_int($idn)) {
        $name = IPS_GetName($idn);
        $this->label = str_replace(" ", "_", substr($name, 1, strlen($name)));
        $this->id = $idn;
      }
      else
      {
        $this->label = str_replace(" ", "_", $idn);
        unset($this->type);
        unset($this->isVerbose);
        unset($this->id);
      }
  }



  public function ObjectInfo()
  {
      echo "Information dump for $".$this->label." :\n";
      echo "*****************************\n";
      echo "CLASS: " , get_class($this) , "\n";
      echo "*****************************\n";
      echo "            METHODS          \n";
      $class_methods = get_class_methods($this);
      foreach ($class_methods as $method_name) {echo "$method_name\n"; }
      echo "*****************************\n";
      echo "            PARAMETERS           \n";
      print_r(get_object_vars($this));

  }

  public function SetVerbose($par)      { $this->isVerbose = $par; return $this->isVerbose; }
  public function IsVerbose()           { return $this->isVerbose; }
  public function GetId()               { return $this->id; }
  public function GetLabel()            { return $this->label; }
  public function GetType()            { return $this->type; }
  public function getLastChange()       {return 0; }

  public function persist() {
    $path = getcwd() . "\\..\\sox\\objects\\" . $this->label . '.object.sox';
    $so = serialize($this);
    file_put_contents($path, $so);
  }

  public function retrieve() {
    $path = getcwd() . "\\..\\sox\\objects\\" . $this->label . '.object.sox';
    $no = unserialize(file_get_contents($path));
    $objVars = get_object_vars($no);
    foreach ($objVars as $varName => $value) $this->$varName = $no->$varName;
  }

  public function destroy() {
    $path = getcwd() . "\\..\\sox\\objects\\" . $this->label . '.object.sox';
    unlink($path);
  }

}

