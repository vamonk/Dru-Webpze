<?php

//DB Sybase SQL Anywhere Class @0-63838224
/*
 *  Database Management for PHP
 *
 *  Hier geht es auch darum, Standard-MySQL-Funktionsaufrufe verwenden zu können,
 *  es ist hier eine Art Mapping.
 *
 * db_sqlanywhere.php
 * $dbh = odbc_connect('emico11', 'dba', 'sql', SQL_CUR_USE_ODBC);
 *
 */ 

class db_odbc {

    var $Persistent = false;
    var $Uppercase  = false;

    var $DbHandle  = 0;
    var $Query_ID = 0;
    var $Record   = array();
    var $Row;

    var $Auto_Free = 1;     ## Set this to 1 for automatic odbc_free_result()
    var $Connected = false;


    /* public: constructor */
    function DB_Sql($query = "") {
        $this->query($query);
    }

    function try_connect() {
        $this->Query_ID  = 0;
        if ($this->Persistent)
            $this->DbHandle = @odbc_pconnect(DB_DSN ,DB_USER,DB_PASSWORD,SQL_CUR_USE_ODBC);
        else
            $this->DbHandle = @odbc_pconnect(DB_DSN ,DB_USER,DB_PASSWORD,SQL_CUR_USE_ODBC);
            $this->Connected = $this->DbHandle ? true : false;
            return $this->Connected;
    }

    function connect() {
    if (!$this->Connected) {
        $this->Query_ID  = 0;
          if ($this->Persistent)
              $this->DbHandle = @odbc_pconnect(DB_DSN ,DB_USER,DB_PASSWORD,SQL_CUR_USE_ODBC);
          else
              // $this->DbHandle = @odbc_pconnect(DB_DSN ,DB_USER,DB_PASSWORD,SQL_CUR_USE_ODBC);
              $this->DbHandle = odbc_connect(DB_DSN ,DB_USER,DB_PASSWORD,SQL_CUR_USE_ODBC);
            if (!$this->DbHandle) {
                $this->Halt("Kann keine ODBC-Verbindung herstellen! DSN: ". DB_DSN . " User: ". DB_USER. " Passwort: " .DB_PASSWORD );
                return false;
          }
          $this->Connected = true;
        }
    }

    function prepare($Query_String){
        if ($Query_String == "")
        return 0;
        $this->connect();
        if (!$this->DbHandle) {
            print "<font color='red'>FEHLER: Keine Connection-ID</font>";
        }
        $statement = odbc_prepare( $this->DbHandle,  $Query_String );
        if (!$statement) {
            print "<font color='red'>FEHLER mit diesen Statement: <br></font>$Query_String";
        }
        return $statement;
    }

    function execute($statement) {
        if ($statement == "")
        return false;
        $success = odbc_execute( $statement );
        if (!$success) {
            print "<font color='red'>FEHLER mit diesen Statement: <br></font>$statement";
        }
        return $success;
    }

    function query($Query_String) {
        if ($Query_String == "")
            return 0;
        $this->connect();
        // New query, discard previous results.
        if ($this->Query_ID) {
            $this->free_result();
        }


// echo 'Debug:<br><pre>' . $Query_String . '</pre><br><br>';
        $this->Query_ID = odbc_exec($this->DbHandle, $Query_String);
        $this->Row = 0;
        if (!$this->Query_ID) {
           //  $this->Errors->addError("Database error: Invalid SQL " . $Query_String);
        }

        return $this->Query_ID;
    }

    function affected_rows() {
        return odbc_num_rows( $this->DbHandle );
    }
    function fetch_array ($res){
       return odbc_fetch_array($res);
    }

  function next_record() {
    if (!$this->Query_ID)
      return 0;
    $this->Record = odbc_fetch_row($this->Query_ID);
    $stat = is_array($this->Record);
    if ($stat) {
      $this->Row++;
      $count = odbc_num_fields($this->Query_ID);
      for ($i = 0; $i < $count; $i++) {
        // TODO $fieldinfo = odbc_fetch_field($this->Query_ID, $i);
        // TODO $fieldname = ($this->Uppercase) ? strtoupper($fieldinfo->name) : $fieldinfo->name;
        // TODO $this->Record[$fieldname] = $this->Record[$i];
      }
    } else if ($this->Auto_Free) {
      $this->free_result();
    }
    return $stat;
  }

  function free_result() {
    if (is_resource($this->Query_ID)) {
        odbc_free_result($this->Query_ID);
    }
    $this->Query_ID = 0;
  }

  function seek($pos) {
    // TODO $status = @odbc_data_seek($this->Query_ID, $pos);
    // TODO if ($status) $this->Row = $pos;
    return true;
  }

  /* TODO function num_rows($statement) {
    return odbc_stmt_num_rows($statement);
  }
  */


  function num_fields() {
    return odbc_num_fields($this->Query_ID);
  }

  function f($Name) {
    if($this->Uppercase) $Name = strtoupper($Name);
    return $this->Record && array_key_exists($Name, $this->Record) ? $this->Record[$Name] : "";
  }

  function p($Name) {
    if($this->Uppercase) $Name = strtoupper($Name);
    print $this->Record[$Name];
  }

  function close() {
    if ($this->Query_ID) {
      $this->free_result();
    }
    if ($this->Connected) {
      odbc_close_all();
      $this->Connected = false;
    }
  }


  function halt($msg) {
    printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
    printf("<b>SQL Anywhere Error</b><br>\n");
    die("Session halted.");
  }

  function esc($value) {
    return str_replace("'", "''", $value);
  }
}

//End DB Sybase SQL Anywhere Class


?>
