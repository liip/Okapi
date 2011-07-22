<?php
/**
 * Default DB Connection Factory for MySQLi Connections
 */
class api_db_mysqli implements api_Idb {

	protected $db;
	protected $cfg;

    /**
     * Open a database connection based on config settings.
     */
    public function getDBConnection($cfg)
    {
        if (!$cfg) {
            throw new api_exception_Db(api_exception::THROW_FATAL, null, null, "Cannot find database configuration settings");
        }

		$this->cfg = $cfg;

		$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['dbname']);

        if (mysqli_connect_errno()) {
            throw new api_exception_Db(api_exception::THROW_FATAL, null, null, "Could not open database connection {$cfg['name']}: ".$db->connect_error.", host information: ".$db->host_info);
        }

		if (isset($cfg['charset'])) {
			$db->query('SET NAMES \''.$cfg['charset'].'\'');
		} else {
			$db->query('SET NAMES \'utf8\'');
		}

		$this->db = $db;
        return $this;
    }

    public function query($sql, $params = array()) {
    	if (count($params)) {
    		$sql = $this->replaceParams($sql, $params);
    	}
		if (!($res = $this->db->query($sql))) {
			throw new api_exception_Db(api_exception::THROW_FATAL, null, null, "Query execution error: '".$sql."', message: ".$this->db->error);
	    }
		return $res;
    }

    public function lastInsertId()
	{
		return (string) $this->db->insert_id;
	}

	public function getConnection()
	{
		return $this->db;
	}

	public function getConfig()
	{
		return $this->cfg;
	}

	public function quoteIdentifier($id) {
		return '`'.$id.'`';
	}

    public function insert($table, array $bind)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col);
            $vals[] = '?';
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';

        // execute the statement and return the number of affected rows
        if (!$this->query($sql, array_values($bind))) {
        	return false;
        }
        return $this->db->affected_rows;
    }

    public function update($table, array $bind, $where = '')
    {
        // Build "col = ?" pairs for the statement,
        $set = array();
        foreach ($bind as $col => $val) {
            $val = '?';
            $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
        }

        /**
         * Build the UPDATE statement
         */
        $sql = "UPDATE "
             . $this->quoteIdentifier($table, true)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');

        // Execute the statement and return the number of affected rows
        if (!$this->query($sql, array_values($bind))) {
        	return false;
        }
        return $this->db->affected_rows;
    }

    public function delete($table, $where = '')
    {
        /**
         * Build the DELETE statement
         */
        $sql = "DELETE FROM "
             . $this->quoteIdentifier($table, true)
             . (($where) ? " WHERE $where" : '');

        // execute the statement and return the number of affected rows
        if (!$this->query($sql)) {
        	return false;
        }
        return $this->db->affected_rows;
    }

    public function fetchAll($sql, $bind = array())
    {
        $res = $this->query($sql, $bind);
        $return = array();
        while($row = $res->fetch_assoc()) {
        	$return[] = $row;
        }
        return $return;
    }

    public function fetchRow($sql, $bind = array())
    {
        $res = $this->query($sql, $bind);
        return $res->fetch_assoc();
    }

    public function fetchOne($sql, $bind = array())
    {
        $res = $this->query($sql, $bind);
        if (!$res->num_rows) {
        	return false;
        }
        $arr = $res->fetch_row();
        if (isset($arr[0])) {
	        return $arr[0];
        }
    	return false;
    }

    public function quote($value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val);
            }
            return implode(', ', $value);
        }

		if (is_int($value) || is_float($value)) {
			return $value;
		}
        return '"'.$this->db->escape_string($value).'"';
    }

	// TODO public function beginTransaction()
	// TODO public function commit()
	// TODO public function rollBack()

	protected function replaceParams($sql, $params)
	{
		// store the amount of ?'s
		$cnt=substr_count($sql, '?');

		if (is_array($params) || $cnt > 1) {
			// array & more than one ? to replace -> loop through them
			if (count($params) !== $cnt) {
				throw new api_exception_Db(api_exception::THROW_FATAL, array(), null, 'Parameter/placeholder amounts mismatch for query : '.$sql);
			}
			while (--$cnt >= 0) {
				if (isset($last)) {
					$last = strrpos($sql, '?', - (strlen($sql) - $last + 1));
				} else {
					$last = strrpos($sql, '?');
				}
				if(is_object($params[$cnt])) {
					$params[$cnt] = $params[$cnt]->__toString();
				}
				$sql = substr_replace($sql, $this->quote($params[$cnt]), $last, 1);
			}
			return $sql;
		} elseif (!empty($params)) {
			// no array -> replace the first ? by the string
			if (is_object($params)) {
				$params = $params->__toString();
			}
			return substr_replace($sql, $this->quote($params), strpos($sql, '?'), 1);
		}
		// no params to replace, return original query
		return $sql;
	}
}