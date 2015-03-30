<?
class SOX_Player extends SOX_Object {

  public function AddFile($fpath)           { return WAC_AddFile($this->id, $fpath); }
  public function ClearPlaylist()           { return WAC_ClearPlaylist($this->id); }
  public function GetPlaylistLength()       { return WAC_GetPlaylistLength($this->id); }
  public function GetPlaylistPosition()     { return WAC_GetPlaylistPosition($this->id); }
  public function SetPlaylistPosition($pos) { return WAC_SetPlaylistPosition($this->id, $pos); }

  public function SetVolume($vol = 50)        { return WAC_SetVolume($this->id, $vol); }
  public function SetShuffle($shuffle = true) { return WAC_SetShuffle($this->id, $shuffle); }
  public function SetRepeat($rep = true)      { return WAC_SetRepeat($this->id, $rep); }
  public function SetPosition($pos = 0)       { return WAC_SetPosition($this->id, $pos); }

  public function PlayFile($fpath)            { return WAC_PlayFile($this->id, $fpath); }

  public function Play()  { return WAC_Play($this->id); }
  public function Stop()  { return WAC_Stop($this->id); }
  public function Pause() { return WAC_Pause($this->id); }
  public function Prev()  { return WAC_Prev($this->id); }
  public function Next()  { return WAC_Next($this->id); }

  public function AddList($dirPath = '') {
    $fileList = SOXBase::listDir($dirPath);
    foreach ($fileList as $filePath) {
      $this->AddFile($filePath);
    }
  }
}
