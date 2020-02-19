<?php
namespace Civi\Api4\Action\System;

use Civi\Api4\Utils\LogLimitTrait;

class PruneLogTables extends \Civi\Api4\Generic\AbstractAction {

  use LogLimitTrait;

  /**
   * List of tables to prune (comma-separated in square brackets); if empty, prune all tables
   *
   * @var array
   */
  protected $tableNames = [];

  /**
   * Cutoff date: all log table rows older than this are deleted
   *
   * @var string
   * @required
   */
  protected $cutoffDate;

  public function _run(\Civi\Api4\Generic\Result $result) {
    $this->refresh();
    $logDir = \Civi::paths()->getVariable('civicrm.log', 'path');
    \Civi::log()->debug("PruneLogTables: logDir $logDir, cutoff date " . $this->cutoffDate);

    if (($cutoff = strtotime($this->cutoffDate)) === FALSE) {
      $error = "PruneLogTables: not a valid date: " . $this->cutoffDate;
      \Civi::log()->error($error);
      throw new \API_Exception($error);
    }

    if (count($this->tableNames) == 0) {
      $civiDBName = \CRM_Core_DAO::getDatabaseName();
      $dao = \CRM_Core_DAO::executeQuery("
        SELECT     T.TABLE_NAME
        FROM       INFORMATION_SCHEMA.TABLES T
        INNER JOIN INFORMATION_SCHEMA.COLUMNS C ON T.TABLE_SCHEMA=C.TABLE_SCHEMA AND T.TABLE_NAME=C.TABLE_NAME AND LOWER(C.COLUMN_NAME)='log_date'
        WHERE      T.TABLE_SCHEMA = '{$civiDBName}'
        AND        T.TABLE_TYPE = 'BASE TABLE'
        AND        T.TABLE_NAME LIKE 'log_civicrm_%'
      ");
      while ($dao->fetch()) {
        $this->tablesNames[] = $dao->TABLE_NAME;
      }
    }

    foreach ($this->tableNames as $table) {
      // Would be nice to return the number of rows deleted, but the comments for
      // CRM_Utils_SQL_Delete.execute() seem inaccurate; calling fetchAll() does
      // not give anything useful.
      $dao = \CRM_Utils_SQL_Delete::from($table)->where('unix_timestamp(log_date) < #cutoff')->param('#cutoff', $cutoff)->execute();
      $result[] = [
        'table' => $table,
      ];
    }
  }

  /**
   * Fields returned by the method
   *
   * @return array
   */
  public static function fields() {
    return [
      ['name' => 'table', 'data_type' => 'String'],
    ];
  }

}
