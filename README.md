# org.nubay.cleanup

This extension provides tools for deleting old logfiles and log table data.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM v5.16+

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl org.nubay.cleanup@https://github.com/Nubay-Services/org.nubay.cleanup/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
git clone https://github.com/Nubay-Services/org.nubay.cleanup.git
cv en cleanup
```

## Usage

The extension exposes two new API4 methods, that can be run using command-line tools like cv, or via the
CiviCRM API4 Explorer.

The `System.prunelogfiles` API method will delete files from the `[civicrm.log]` directory
(usually "ConfigAndLog") that are older than the cutoff date.

```bash
cv api4 System.prunelogfiles '{"cutoffDate":"2018/1/1"}'
```

The `System.prunelogtables` API method will delete rows from CiviCRM `log_` tables that are older than the
cutoff date. Specific tables can be given with the `tableNames` parameter; if not provided, all `log_`
tables will be pruned.

```bash
cv api4 System.prunelogtables '{"tableNames":["log_civicrm_contact","log_civicrm_group"],"cutoffDate":"2018/1/1"}'
cv api4 System.prunelogtables '{"cutoffDate":"2018/1/1"}'
```

Note that if the `log_` tables were created by a version of CiviCRM older than 5.16, then you must convert
them to the InnoDB storage engine (older versions of CiviCRM used the ARCHIVE engine, which does not allow
rows to be deleted). Do this by executing the following API method:

```bash
cv api System.updatelogtables forceEngineMigration=1
```

Alternatively, you can use the following extension instead to convert the tables to InnoDB:
https://github.com/eileenmcnaughton/nz.co.fuzion.innodbtriggers.

## Known Issues

See https://github.com/Nubay-Services/org.nubay.cleanup/issues.
