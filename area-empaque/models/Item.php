<?php
class Item {
    private $file;
    public function __construct($path = __DIR__.'/../data/items.json'){
        $this->file = $path;
        if(!file_exists($this->file)) file_put_contents($this->file, json_encode([]));
    }
    private function read(){ $c = file_get_contents($this->file); return json_decode($c, true) ?? []; }
    private function write($arr){ return file_put_contents($this->file, json_encode(array_values($arr), JSON_PRETTY_PRINT)); }
    public function all(){ return $this->read(); }
    public function find($id){ foreach($this->read() as $r) if($r['id']==$id) return $r; return null; }
    public function create($data){ $all=$this->read(); $id = (count($all)? max(array_column($all,'id'))+1 : 1); $data['id']=$id; $all[]=$data; $this->write($all); return $data; }
    public function update($id,$data){ $all=$this->read(); foreach($all as &$r) if($r['id']==$id){ $r=array_merge($r,$data); $this->write($all); return $r;} return false; }
    public function delete($id){ $all=$this->read(); $n=[]; $found=false; foreach($all as $r){ if($r['id']==$id){ $found=true; continue;} $n[]=$r;} $this->write($n); return $found; }
}
