<?php
/*
 * pgBackupRestore v2 
 * Date: 30th November 2007
 * Author: Michele Brodoloni <michele.brodoloni@xtnet.it>
 * 
 * Changelog:
 * - Fixed issue with bytea fields
 * - Fixed issue with empty values in NOT NULL fields
 * - Added custom header
 * - Added 2 more options to backup data preserving database structure (DataOnly, UseTruncateTable)
 * - Added some default statements included in every backup file (~ line 227)
 * - Added encoding support 
 * - Improved error checking
 */
include('../../RedirectModulesInc.php');
class pgBackupRestore
{
   //------------------------------------//
   //---[  Configuration variables   ]---//
   //---| SET THEM FROM YOUR SCRIPT  |---//
   //------------------------------------//
  
   // Header to be written on file
   var $Header = "";

   // Remove comments from SQL file ( pgBackupRestore::commentSQL() method )
   var $StripComments = false;

   // Include table names into INSERT statement
   var $UseCompleteInsert = false;

   // Drop the table before re-creating it
   var $UseDropTable = true;

   // Adds TRUNCATE TABLE statement (for data only dump)
   var $UseTruncateTable = false;

   // Dump table structure only, not data
   var $StructureOnly = false;

   // Dump only table data without structure
   var $DataOnly = false;

   // Script keeps running after encountering a fatal error
   var $IgnoreFatalErrors = false;

   // Database Encoding 
   // (Supported are: SQL_ASCII and UTF8. Unknown behaviour with others.)
   var $Encoding = "SQL_ASCII";

   //------------------------------------//
   //---| NO NEED TO EDIT BELOW HERE |---//
   //------------------------------------//

   //---[ File related variables
   var $fpSQL;

   //---[ Database related variables   
   var $Connected = false;
   var $Database;
   var $Link_ID;
   var $Query_ID;
   var $Record = array();
   var $Tables = array();
   var $BackupOnlyTables = array();
   var $ExcludeTables = array();
   var $Row = 0;
   
   //---[ Error Handling
   var $GotSQLerror = false;
   var $LastSQLerror = "";

   //---[ Protected keywords
   var $pKeywords = array("desc");

   # CLASS CONSTRUCTOR
   function pgBackupRestore($uiHost, $uiUser, $uiPassword, $uiDatabase, $uiPort = 5432)
   {
      $this->Link_ID = pg_pconnect("host=${uiHost} port=${uiPort} dbname=${uiDatabase} user=${uiUser} password=${uiPassword}");
      if (!$this->Link_ID)
         $this->Error("Can't connect to the Postgres Database", true);
      $this->Database = $uiDatabase;
      $this->Connected = ($this->Link_ID) ? true : false;
      pg_set_client_encoding($this->Link_ID, $this->Encoding);
   }

   function _FixOptions()
   {
      // Checks and fix for incompatible options
      if ($this->StructureOnly)
      {
          $this->DataOnly = false;
          $this->UseTruncateTable = false;
      }

      if ($this->DataOnly)
      {
         $this->StructureOnly = false;
         $this->UseDropTable = false;
      }
   }

   #------------------------#
   # SQL RELATIVE FUNCTIONS #
   #------------------------#
   
   // Queries the PostgreSQL database.
   // If a SQL error is encountered it will be written on 
   // $this->LastSQLerror variable and $this->GotSQLerror 
   // will be set to TRUE. Returns the query id.
   //
   function query($uiSQL)
   {
      if (!$this->Connected) return (false);
      $this->Row = 0;
      $this->Query_ID = @pg_query($this->Link_ID, $uiSQL);
      $this->LastSQLerror = trim(str_replace("ERROR:", "", pg_last_error($this->Link_ID)));
      $this->GotSQLerror = ($this->LastSQLerror) ? true : false;
      return $this->Query_ID;
   }

   // Returns the next record of a query resultset.
   // Values can be accessed through $this->Record[field_name]
   // or by $this->Record[field_id] (see pg_fetch_array())
   //
   function next_record()
   {
      if (!$this->Query_ID) return (false);

      $this->Record = @pg_fetch_array($this->Query_ID, $this->Row++);
      if (is_array($this->Record)) 
         return(true);
      else 
      {      
         pg_free_result($this->Query_ID);
         $this->Query_ID = 0;
         return(false);
      }
   }

   // Returns a value from a record.
   // Just pass the wanted field name to this.
   //
   function get($uiField)
   {
      if (is_array($this->Record) && array_key_exists($uiField, $this->Record))
         return $this->Record[$uiField];
      else
         return (NULL);
   }
   
   // Returns an array containing the field names
   // returned by a query. 
   // Useful when doing a "SELECT * FROM table" query
   //
   function field_names()
   {
      if (!$this->Query_ID) return(false);
      $n = @pg_num_fields($this->Query_ID);
      $columns = Array();

      for ($i=0; $i<$n ; $i++ )
         $columns[] = @pg_field_name($this->Query_ID, $i);

      return $columns;
   }

   // Return a quoted string if the $this->pKeywords array
   // contains it. It is used when a table name match
   // a PostgreSQL keyword such as "DESC", "PRIMARY"
   // and others, causing a SQL syntax error when restoring
   //
   function escape_keyword($uiKeyword)
   {
      if (in_array($uiKeyword, $this->pKeywords))
         return('"'.$uiKeyword.'"');
      else
         return($uiKeyword);
   }

   #--------------------------#
   # CLASS RELATIVE FUNCTIONS #
   #--------------------------#
   
   // Writes text into the SQL file
   // Called within $this->Backup() method.
   //
   function writeSQL($uiString)
   {
      if (!$this->fpSQL) return(false);
      fwrite($this->fpSQL, $uiString);
   }

   // Writes comments into the SQL file when
   // $this->StripComments is set to FALSE
   // Called within $this->Backup() method.
   // 
   function commentSQL($uiComment)
   {
      if (!$this->fpSQL) return(false);

      if (!$StripComments)
         $this->writeSQL("-- $uiComment");
   }

   // Creates a SQL file containing structure, data, indexes
   // relationships, sequences and so on..
   //
   function Backup($uiFilename = NULL)
   {
      if (!$this->Connected) return (false);

      if (is_null($uiFilename))
         $this->Filename = $this->Database.".sql";
      else
         $this->Filename = $uiFilename;

      // Fix incompatible flags
      $this->_FixOptions();

      //---[ PASS 1: Opening SQL File for writing
   
      $this->fpSQL = @fopen($this->Filename, "w");
      if (!$this->fpSQL)
         $this->Error("Can't open ". $this->Filename ." for writing!", true);
      
      // Writes header to file if string Header is not empty
      if(!empty($this->Header)) $this->writeSQL($this->Header."\n");
       
      //---[ PASS 1.1: Set default options
      $this->commentSQL("Default options\n");
      $this->writeSQL("SET client_encoding = '{$this->Encoding}';\n");
      $this->writeSQL("SET standard_conforming_strings = _off;\n");
      $this->writeSQL("SET check_function_bodies = false;\n");
      $this->writeSQL("SET client_min_messages = _warning;\n");
      $this->writeSQL("SET escape_string_warning = _off;\n");
      $this->writeSQL("\n");
      
      //---[ PASS 2: Obtaining table list from database 
      // If the tables array is not empy, it means that
      // the method $this->BackupOnlyTables was used
      if (empty($this->Tables))
      {
         $SQL = "SELECT relname AS tablename\n".
                "FROM pg_class WHERE relkind IN ('r')\n".
                "AND relname NOT LIKE 'pg_%' AND relname NOT LIKE 'sql_%' ORDER BY tablename\n";
         $this->query($SQL);
     
         // Checks if the current table is in the exclude array. 
         while ($this->next_record())
         {
            $Table = $this->get("tablename");
            if (!in_array($Table, $this->ExcludeTables))
               $this->Tables[] = $this->escape_keyword($Table);
         }
      } 
  
      //---[ PASS 3: Generating structure for each table
      foreach($this->Tables as $Table)
      {
         // Use DROP TABLE statement before INSERT ?
         if ($this->UseDropTable)
            $this->writeSQL("DROP TABLE IF EXISTS ${Table} CASCADE;\n");
         elseif ($this->UseTruncateTable)
            $this->writeSQL("TRUNCATE TABLE ${Table};\n");

         if (!$this->DataOnly)
         {
            $_sequences = array();
            
            $this->commentSQL("Structure for table '${Table}'\n");

            $strSQL .= "CREATE TABLE ${Table} (";
         
            $SQL = "SELECT attnum, attname, typname, atttypmod-4 AS atttypmod, attnotnull, atthasdef, adsrc AS def\n".
                   "FROM pg_attribute, pg_class, pg_type, pg_attrdef\n".
                   "WHERE pg_class.oid=attrelid\n".
                   "AND pg_type.oid=atttypid AND attnum>0 AND pg_class.oid=adrelid AND adnum=attnum\n".
                   "AND atthasdef='t' AND lower(relname)='${Table}' UNION\n".
                   "SELECT attnum, attname, typname, atttypmod-4 AS atttypmod, attnotnull, atthasdef, '' AS def\n".
                   "FROM pg_attribute, pg_class, pg_type WHERE pg_class.oid=attrelid\n".
                   "AND pg_type.oid=atttypid AND attnum>0 AND atthasdef='f' AND lower(relname)='${Table}'\n";
            $this->query($SQL);
            while ( $this->next_record() )
            {
               $_attnum     = $this->get('attnum');
               $_attname    = $this->escape_keyword( $this->get('attname') );
               $_typname    = $this->get('typname');
               $_atttypmod  = $this->get('atttypmod'); 
               $_attnotnull = $this->get('attnotnull');
               $_atthasdef  = $this->get('atthasdef');
               $_def        = $this->get('def');     

               if (preg_match("/^nextval/", $_def))
               {
                  $_t = explode("'", $_def);
                  $_sequences[] = $_t[1];
               }

               $strSQL .= "${_attname} ${_typname}";
               if ($_typname == "varchar") $strSQL .= "(${_atttypmod})";
               if ($_attnotnull == "t")    $strSQL .= " NOT NULL";
               if ($_atthasdef == "t")     $strSQL .= " DEFAULT ${_def}";
               $strSQL .= ","; 
            }
            $strSQL  = rtrim($strSQL, ",");
            $strSQL .= ");\n";

            //--[ PASS 3.1: Creating sequences
            if ($_sequences)
            {
               foreach($_sequences as $_seq_name)
               {
                  $SQL = "SELECT * FROM ${_seq_name}\n";
                  $this->query($SQL);
                  $this->next_record();
               
                  $_incrementby = $this->get('increment_by');
                  $_minvalue    = $this->get('min_value');
                  $_maxvalue    = $this->get('max_value');
                  $_lastvalue   = $this->get('last_value');
                  $_cachevalue  = $this->get('cache_value');

                  $this->writeSQL("CREATE SEQUENCE ${_seq_name} INCREMENT ${_incrementby} MINVALUE ${_minvalue} ".
                                  "MAXVALUE ${_maxvalue} START ${_lastvalue} CACHE ${_cachevalue};\n");
              }
            }
            $this->writeSQL($strSQL);
         }
  
         if (!$this->StructureOnly || $this->DataOnly)
         {  
            $field_attribs = array();
            //---[ PASS 4: Generating INSERTs for data
            $this->commentSQL("Data for table '${Table}'\n");
         
            //---[ PASS 4.1: Get field attributes to check if it's null or bytea (to be escaped)
            $SQL = "SELECT * FROM ${Table} LIMIT 0;\n";
            $this->query($SQL);
            $fields = $this->field_names();

            foreach ($fields as $Field)
               $field_attribs[$Field] = $this->GetFieldInfo($Table, $Field);
            //---| END PASS 4.1

            $SQL = "SELECT * FROM ${Table}\n";
            $this->query($SQL);

            while ( $this->next_record() )
            {
               $Record = array();
               foreach($fields as $f)
               {
                  $data = $this->get($f);
                  if ($field_attribs[$f]['is_binary'])
                  {  // Binary Data
                     $Record[$f] = addcslashes(pg_escape_bytea($data),"\$");
                  }
                  else
                  {  // Strings
                     $data = preg_replace("/\x0a/", "", $data);
                     $data = preg_replace("/\x0d/", "\r", $data);
                     $Record[$f] = pg_escape_string(trim($data));
                  }
               }
               $FieldNames = ($this->UseCompleteInsert) ?  "(".implode(",",$fields).")" : "";
               
               $strSQL = "INSERT INTO ${Table}${FieldNames} VALUES({". (implode("},{",$fields))."});";
               foreach($fields as $f)
               {
                  if ($Record[$f] != '')
                     $str = sprintf("'%s'", $Record[$f]);
                  else
                     $str = ($field_attribs[$f]['not_null']) ? "''" : "NULL";
                     
                  $strSQL = preg_replace("/{".$f."}/", $str, $strSQL);
               }
               $this->writeSQL($strSQL."\n");
               unset($strSQL);
            }
         }

         if (!$this->DataOnly)
         {
            //---[ PASS 5: Generating data indexes (Primary)
            $this->commentSQL("Indexes for table '${Table}'\n");

            $SQL = "SELECT pg_index.indisprimary, pg_catalog.pg_get_indexdef(pg_index.indexrelid)\n".
                   "FROM pg_catalog.pg_class c, pg_catalog.pg_class c2, pg_catalog.pg_index AS pg_index\n".
                   "WHERE c.relname = '${Table}'\n".
                   "AND c.oid = pg_index.indrelid\n".
                   "AND pg_index.indexrelid = c2.oid\n";
            $this->query($SQL);
            while ( $this->next_record() )
            {
               $_pggetindexdef = $this->get('pg_get_indexdef');
               $_indisprimary = $this->get('indisprimary');

               if (eregi("^CREATE UNIQUE INDEX", $_pggetindexdef))
               {
                  $_keyword = ($_indisprimary == 't') ? 'PRIMARY KEY' : 'UNIQUE';
                  $strSQL = str_replace("CREATE UNIQUE INDEX", "" , $this->get('pg_get_indexdef'));
                  $strSQL = str_replace("USING btree", "|", $strSQL);
                  $strSQL = str_replace("ON", "|", $strSQL);
                  $strSQL = str_replace("\x20","", $strSQL);
                  list($_pkey, $_tablename, $_fieldname) = explode("|", $strSQL);
                  $this->writeSQL("ALTER TABLE ONLY ${_tablename} ADD CONSTRAINT ${_pkey} ${_keyword} ${_fieldname};\n");
                  unset($strSQL);
               } 
               else $this->writeSQL("${_pggetindexdef};\n");
            }
            
            //---[ PASS 6: Generating relationships
            $this->commentSQL("Relationships for table '${Table}'\n");
         
            $SQL = "SELECT cl.relname AS table, ct.conname, pg_get_constraintdef(ct.oid)\n".
                   "FROM pg_catalog.pg_attribute a\n".
                   "JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = 'r')\n".
                   "JOIN pg_catalog.pg_namespace n ON (n.oid = cl.relnamespace)\n".
                   "JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND ct.confrelid != 0 AND ct.conkey[1] = a.attnum)\n".
                   "JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = 'r')\n".
                   "JOIN pg_catalog.pg_namespace nf ON (nf.oid = clf.relnamespace)\n".
                   "JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND af.attnum = ct.confkey[1]) order by cl.relname\n";
            $this->query($SQL);
            while ( $this->next_record() )
            {
               $_table   = $this->get('table');
               $_conname = $this->get('conname');
               $_constraintdef = $this->get('pg_get_constraintdef');
               $this->writeSQL("ALTER TABLE ONLY ${_table} ADD CONSTRAINT ${_conname} ${_constraintdef};\n");
            }
         }
      }
      //---[ PASS 7: Closing SQL File
      fclose($this->fpSQL);

      return (filesize($this->Filename) > 0)? true : false;
   }

    // Checks if a field can be null, in order to replace it with '' or NULL
    // when building backup SQL statements
   function GetFieldInfo($uiTable, $uiField)
   {

      if (!$this->Connected) return(false);
      $response = array();

      $SQL = "SELECT typname, attnotnull \n".
             "FROM pg_attribute, pg_class, pg_type WHERE pg_class.oid=attrelid \n".
             "AND pg_type.oid=atttypid AND attnum>0 AND lower(relname)='${uiTable}' and attname = '${uiField}';\n";

      $this->query($SQL);
      $this->next_record();
      
      $not_null   = $this->get('attnotnull');
      $field_type = $this->get('typname');
      
      $response['not_null']  = ($not_null == 't') ? true : false;
      $response['is_binary'] = ($field_type == 'bytea') ? true : false;

      return $response;
   }

   // Restore the database from a SQL file
   //
   function Restore($uiFilename = NULL)
   {
      $this->Errors = array();
      if (!$this->Connected) return(false);

      if (is_null($uiFilename))
         $this->Filename = $this->Database.".sql";
      else
         $this->Filename = $uiFilename;


      if (!is_readable($this->Filename))
         $this->Error("Can't find {$this->Filename} for opening", true);

      $_CurrentLine = 0;
      $_fpSQL = fopen($this->Filename, "r");
      while ( $_readSQL = fgets($_fpSQL) )
      {
         $_CurrentLine++;
         if (preg_match("/^-/", $_readSQL) || preg_match("/^[\s]+$/", $_readSQL)) continue; // Don't bother about comments and blank lines
         if ($this->Encoding == 'UTF8')
            $this->query(utf8_encode($_readSQL));
         else
            $this->query($_readSQL);
         if ($this->GotSQLerror)
            $this->Error("SQL syntax error on line ${_CurrentLine} (". $this->LastSQLerror .")", true);
      }
   }

   // Use this method when you don't need to backup
   // some specific tables. The passed value can
   // be a string or an array.
   //
   function ExcludeTables($uiTables)
   {
      if (empty($uiTables)) return(false);

      if (is_array($uiTables))
         foreach ($uiTables as $item)
            $this->ExcludeTables[] = $item;
      else
         $this->ExcludeTables[] = $uiTables; 
   } 

   // Use this methon when you need to backup
   // ONLY some specific tables. The passed value
   // can be a string or an array.
   //
   function BackupOnlyTables($uiTables)
   {
      if (empty($uiTables)) return(false);

      if (is_array($uiTables))
         foreach ($uiTables as $item)
            $this->Tables[] = $item;
      else
         $this->Tables[] = $uiTables;
   }

   // Error printing function.
   // When outputting a fatal error it will exit the script.
   // php-cli coloured output included ;)
   //
   function Error($uiErrStr, $uiFatal = false)
   {
      $_error = "";
      $_error_type = ($uiFatal) ? "Fatal Error" : "Error";
      
      if ($_SERVER['TERM']) // we're using php-cli
         printf("%c[%d;%d;%dm%s: %c[%dm%s\n", 0x1B, 1, 31, 40, $_error_type, 0x1B, 0, $uiErrStr);
      else
         printf("<font face='tahoma' size='2'><b>%s:</b>&nbsp;%s</font><br>\n", $_error_type, $uiErrStr);

      if ($uiFatal && !$this->IgnoreFatalErrors) exit;
   }

}
?>
