<?php

class Database
{
    public $connection;
    private $dns;
    private $username;
    private $password;

    function __construct($file = '../db_config.ini')
    {
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable to open ' . $file . '.');

        $this->dns = $settings['database']['driver'] .
            ':host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['dbname'] .
            ((!empty($settings['database']['charset'])) ? (';charset=' . $settings['database']['charset']) : '');

        $this->username = $settings['database']['username'];
        $this->password = $settings['database']['password'];

        $this->getConnection();
    }

    // get the database connection
    public function getConnection(){

        $this->connection = null;

        try{
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];

            $this->connection = new PDO($this->dns, $this->username, $this->password, $options);
        }catch(PDOException $exception){
            echo "Database connection failed: " . $exception->getMessage();
            die();
        }

        return $this->connection;
    }

    public function select($query, $params = array()) {
        try {
            if ($query != null && explode(' ', strtoupper($query))[0] == 'SELECT') {
                $statement = $this->connection->prepare($query);
                $statement->execute($params);
                $data = $statement->fetchAll();

                return $data;
            } else {
                return [[
                    "code" => 400,
                    "description" => "Only `SELECT` queries allowed in this method!"
                ]];
            }
        } catch (PDOException $err) {
            return [[
                "code" => 400,
                "description" => $err->getMessage()
            ]];
        }
    }

    public function insert($query, $params = array())
    {
        try {
            if ($query != null && explode(' ', strtoupper($query))[0] == 'INSERT') {
                $statement = $this->connection->prepare($query);
                $statement->execute($params);
                $lastId = $this->connection->lastInsertId();

                return $lastId;
            } else {
                return [[
                    "code" => 400,
                    "description" => "Only `INSERT` queries allowed in this method!"
                ]];
            }
        } catch (PDOException $err) {
            return [[
                "code" => 400,
                "description" => $err->getMessage()
            ]];
        }
    }

    public function delete($query, $params = array())
    {
        try {
            if ($query != null &&  explode(' ', strtoupper($query))[0] == 'DELETE') {
                $statement = $this->connection->prepare($query);
                $statement->execute($params);
                $row = $statement->rowCount();
                $status = ($row > 0) ? 'OK' : 'NO CHANGES';

                return $status;
            } else {
                return [[
                    "code" => 400,
                    "description" => "Only `DELETE` queries allowed in this method!"
                ]];
            }
        } catch (PDOException $err) {
            return [[
                "code" => 400,
                "description" => $err->getMessage()
            ]];
        }
    }

    public function update($query, $params = array())
    {
        try {
            if ($query != null &&  explode(' ', strtoupper($query))[0] == 'UPDATE') {
                $statement = $this->connection->prepare($query);
                $statement->execute($params);
                $row = $statement->rowCount();
                $status = ($row > 0) ? 'OK' : 'NO CHANGES';

                return $status;
            } else {
                return [[
                    "code" => 400,
                    "description" => "Only `UPDATE` queries allowed in this method!"
                ]];
            }
        } catch (PDOException $err) {
            return [[
                "code" => 400,
                "description" => $err->getMessage()
            ]];
        }
    }
}