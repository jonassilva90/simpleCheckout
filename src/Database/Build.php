<?php

namespace SimpleCheckout\Database;

class Build {
    public $primaryKey;
    public $from = [];
    public $columns = [];
    public $join = [];
    public $where = [];
    public $order = [];
    public $start;
    public $limit;
    
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    public function joinLeft($table, $first, $operator , $secund){
        $this->join[] = "LEFT JOIN {$table} ON {$first} {$operator} {$secund}";
        return $this;
    }
    public function joinInner($table, $first, $operator , $secund){
        $this->join[] = "INNER JOIN {$table} ON {$first} {$operator} {$secund}";
        return $this;
    }
    public function where($first, $operator , $secund){
        $this->whereRaw("{$first} {$operator} {$secund}");
        return $this;
    }
    public function whereIsNull($field){
        $this->whereRaw("{$field} IS NULL");
        return $this;
    }
    public function whereIsNotNull($field){
        $this->whereRaw("{$field} IS NOT NULL");
        return $this;
    }
    public function whereRaw($where){
        $this->where[] = " {$where} ";
        return $this;
    }
    public function order($field, $order = "ASC"){
        $this->order[] = "{$field} {$order}";
        return $this;
    }
    /** Execulta select
     * 
     * @return boolean|\PDOStatement
     */
    public function get(){
        $sql = "SELECT ";
        if(empty($this->columns))
            $sql .= "*";
        else 
            $sql .= implode(",", $this->columns);
        $sql .= " FROM ";
        $sql .= implode(",", $this->from);
        $sql .= " ";
        $sql .= implode(" \n", $this->join);
        if(!empty($this->where)){
            $sql .= " WHERE ";
            $sql .= implode(" AND ", $this->where);
        }
        if(!empty($this->order)){
            $sql .= " ORDER BY ";
            $sql .= implode(",", $this->order);
        }
        if(!is_null($this->start) && !is_null($this->limit)){
            $sql .= " LIMIT {$this->start},{$this->limit}";
        }
        
        return Query::query($sql);
    }
    public function find($id){
        $this->where($this->primaryKey, '=', $id);
        $result = $this->get();
        if($result->rowCount()==0)
            return false;
        
        return $result->fetch( \PDO::FETCH_ASSOC );
    }
    public function first(){
        $result = $this->get();
        if($result->rowCount()==0)
            return false;
            
        return $result->fetch( \PDO::FETCH_ASSOC );
    }
    public function insert($data){
        return Query::insertByArray($data, $this->from);
    }
    public function update($data){
        return Query::updateByArray($data,$this->from, implode(" AND ", $this->where) );
    }
}
