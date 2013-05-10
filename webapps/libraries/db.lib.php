<?php
class DB{
    
    private $pdo;

    public function __construct($host,$dbname,$port,$user,$password){        
        $dsn = 'mysql:dbname='.$dbname.';host='.$host.';port='.$port;
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->exec('SET CHARACTER SET utf8');
        $this->pdo->exec('SET NAMES utf8');        
    }
    
    public function fetchAll($sql,$params=array()){        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }
    
    public function fetch($sql,$params=array()){        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetch();
    }
    
    public function fetchColumn($sql,$params=array(),$column=0){        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchColumn($column);
    }
    
    public function insert($sql,$params=array()){       
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $id=$this->pdo->lastInsertId();
        if($id)return $id;
        return  $stmt->rowCount();
    }
    
    public function update($sql,$params=array()){       
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return  $stmt->rowCount();
    }
    
    public function delete($sql,$params=array()){       
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return  $stmt->rowCount();
    }

}

$pdo = new DB($cfg['database']['host'],$cfg['database']['dbname'],$cfg['database']['port'], $cfg['database']['user'], $cfg['database']['password']);