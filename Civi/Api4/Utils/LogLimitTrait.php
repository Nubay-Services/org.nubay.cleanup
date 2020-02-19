<?php
namespace Civi\Api4\Utils;

/**
 * This trait keeps track of the errors logged and stops writing them
 * after a maximum is reached, simply to avoid swamping the system with
 * potentially lots of functionally identical messages.
 */
trait LogLimitTrait {
  protected static $_maxErrors = 10;
  protected $_errorCount = 0;
  protected $_logErrors = TRUE;

  public function refresh() {
    $this->_errorCount = 0;
    $this->_logErrors = TRUE;
  }

  public function error($msg) {
    if ($this->_logErrors) {
      \Civi::log()->error($msg);
    }
    if ($this->_errorCount++ >= self::$_maxErrors && $this->_logErrors) {
      \Civi::log()->error("Additional errors will not be logged.");
      $this->_logErrors = FALSE;
    }
  }
}
