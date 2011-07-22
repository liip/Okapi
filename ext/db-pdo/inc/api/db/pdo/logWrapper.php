<?php

class api_db_pdo_logWrapper
{
    private $db;
    private $log;

    /**
     * Constructor
     */
    public function __construct($db, $log) {
        $this->db = $db;
        $this->log = $log;
    }

    public function query($sql, $PDO_FETCH_CLASS = null, $classname = null, $ctorargs = null)
    {
        try {
            $start = microtime(true);
            if ($PDO_FETCH_CLASS) {
                $return = $this->db->query($sql, $PDO_FETCH_CLASS, $classname, $ctorargs);
            } else {
                $return = $this->db->query($sql);
            }
            $end = microtime(true);

            $return = new api_db_pdo_logWrapperStatement($return, $this->log);

            $msg = array(__FUNCTION__.' statement id' => $return->getId(), 'time' => ($end-$start), 'sql' => $sql);
            $this->log->info($msg);
        } catch (Exception $e) {
            $msg = array(__FUNCTION__ => $e->getMessage());
            $this->log->info($msg);
            throw $e;
        }

        return $return;
    }

    public function exec($sql)
    {
        try {
            $start = microtime(true);
            $return = $this->db->exec($sql);
            $end = microtime(true);

            $msg = array(__FUNCTION__.' time' => ($end-$start), 'sql' => $sql);
            $this->log->info($msg);
        } catch (Exception $e) {
            $msg = array(__FUNCTION__ => $e->getMessage());
            $this->log->info($msg);
            throw $e;
        }

        return $return;
    }

    public function prepare($sql, $options = array())
    {
        try {
            $return = $this->db->prepare($sql, $options);
            $return = new api_db_pdo_logWrapperStatement($return, $this->log);

            $msg = array(__FUNCTION__.' statement id' => $return->getId(), 'sql' => $sql);
            $this->log->info($msg);
        } catch (Exception $e) {
            $msg = array(__FUNCTION__.' message' => $e->getMessage());
            $this->log->info($msg);
            throw $e;
        }

        return $return;
   }

    public function __call($method, $args)
    {
        try {
            return call_user_func_array(array($this->db, $method), $args);
        } catch (Exception $e) {
            $msg = array($method.' message' => $e->getMessage());
            $this->log->info($msg);
            throw $e;
        }

        return $return;
    }
}

class api_db_pdo_logWrapperStatement
{
    static public $idcounter = 0;

    private $stmt;
    private $log;
    private $sqlparams = array();
    private $id = null;

    /**
     * Constructor
     */
    public function __construct($stmt, $log, $id = null)
    {
        $this->stmt = $stmt;
        $this->log = $log;
        if (is_null($id)) {
            $id = ++self::$idcounter;
        }
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function bindValue($parameter, $value, $data_type = null)
    {
        $this->sqlparams[$parameter] = $value;
        return $this->stmt->bindValue($parameter, $value, $data_type);
    }

    public function bindParam($parameter, $value, $data_type = null, $length = null, $driver_options = null)
    {
        $this->sqlparams[$parameter] = &$value;
        return $this->stmt->bindValue($parameter, $value, $data_type, $length, $driver_options);
    }

    public function execute($sqlparams = null)
    {
        try {
            $start = microtime(true);
            $return = $this->stmt->execute($sqlparams);
            $end = microtime(true);

            $msg = array(__FUNCTION__.' statement id' => $this->id, 'time' => ($end-$start));
            if (!empty($this->sqlparams)) {
                $msg['params'] = $this->sqlparams;
            } elseif (!empty($sqlparams)) {
                $msg['params'] = $sqlparams;
            }
            if (!empty($msg['params'])) {
                foreach ($msg['params'] as $key => $value) {
                    if (is_resource($value)) {
                        $msg['params'][$key] = (string)$value;
                    }
                }
            }
            $this->log->info($msg);
        } catch (Exception $e) {
            $msg = array(__FUNCTION__.' statement id' => $this->getId(), 'message' => $e->getMessage());
            $this->log->info($msg);
            throw $e;
        }

        return $return;
   }

    public function __call($method, $args)
    {
        try {
            return call_user_func_array(array($this->stmt, $method), $args);
        } catch (Exception $e) {
            $msg = array($method.' statement id' => $this->id, 'message' => $e->getMessage());
            $this->log->info($msg);
            throw $e;
        }

        return $return;
    }
}
