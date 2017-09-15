<?php

namespace SimpleCheckout\Database;

class Model {
    protected $table;
    protected $primaryKey;
    
    public function getTable(){
        return $this->table;
    }
    public function getPrimaryKey(){
        return $this->primaryKey;
    }
    
    static function Build(){
        $m = new static();
        $r = new Build();
        $r->from = $m->getTable();
        $r->primaryKey = $m->getPrimaryKey();
        
        return $r;
    }
    static function select($columns = ['*']){
        $columns = is_array($columns) ? $columns : func_get_args();
        $r = self::Build();
        
        return $r->select($columns);
    }
    
    static function joinLeft($table, $first, $operator , $secund){
        $r = self::Build();
        return $r->joinLeft($table, $first, $operator , $secund);
    }
    static function joinInner($table, $first, $operator , $secund){
        $r = self::Build();
        return $r->joinInner($table, $first, $operator , $secund);
    }
    static function where($first, $operator , $secund){
        $r = self::Build();
        return $r->joinInner($table, $first, $operator , $secund);
    }
    static function whereIsNull($field){
        $r = self::Build();
        return $r->whereIsNull($field);
    }
    static function whereIsNotNull($field){
        $r = self::Build();
        return $r->whereIsNotNull($field);
    }
    static function whereRaw($where){
        $r = self::Build();
        return $r->whereRaw($where);
    }
    static function order($field, $order = "ASC"){
        $r = self::Build();
        return $r->order($field, $order);
    }
    static function order($field, $order = "ASC"){
        $r = self::Build();
        return $r->order($field, $order);
    }
    static function find($id){
        $r = self::Build();
        return $r->find($id);
    }
    static function get(){
        $r = self::Build();
        return $r->get();
    }
    static function insert($data){
        $r = self::Build();
        return $r->insert($data);
    }
    static function update($data){
        $r = self::Build();
        return $r->update($data);
    }
    
}