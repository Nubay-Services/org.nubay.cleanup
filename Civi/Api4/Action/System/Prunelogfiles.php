<?php
namespace Civi\Api4\Action\System;

use Civi\Api4\Utils\LogLimitTrait;

class Prunelogfiles extends \Civi\Api4\Generic\AbstractAction {

  use LogLimitTrait;

  /**
   * Cutoff date: all files older than this are deleted
   *
   * @var string
   * @required
   */
  protected $cutoffDate;

  public function _run(\Civi\Api4\Generic\Result $result) {
    $this->refresh();
    $logDir = \Civi::paths()->getVariable('civicrm.log', 'path');
    \Civi::log()->debug("Prunelogfiles: logDir $logDir, cutoff date " . $this->cutoffDate);

    if (($cutoff = strtotime($this->cutoffDate)) === FALSE) {
      $error = "Prunelogfiles: not a valid date: " . $this->cutoffDate;
      \Civi::log()->error($error);
      throw new \API_Exception($error);
    }

    if ($dh = opendir($logDir)) {
      while (FALSE !== ($file = readdir($dh))) {
        if (($file != '.') && ($file != '..') && ($file != '.htaccess')) {
          $fullFile = $logDir . DIRECTORY_SEPARATOR . $file;
          if (is_dir($fullFile)) {
            continue;
          }

          if (FALSE === ($filemtime = filemtime($fullFile))) {
            $this->error("Prunelogfiles: unable to filemtime file: $fullFile");
          } else {
            $filemtime = date($filemtime);
            if ($filemtime < $cutoff) {
              if (unlink($fullFile) === FALSE) {
                $this->error("Prunelogfiles: unable to unlink file: $fullFile");
              } else {
                $result[] = [
                  'file' => $fullFile,
                ];
              }
            }
          }
        }
      }
      closedir($dh);
    }
  }

  /**
   * Fields returned by the method
   *
   * @return array
   */
  public static function fields() {
    return [
      ['name' => 'file', 'data_type' => 'String'],
    ];
  }

}
