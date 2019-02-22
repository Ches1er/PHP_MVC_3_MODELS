<?php
/**
 * Created by PhpStorm.
 * User: mamedov
 * Date: 13.02.2019
 * Time: 19:39
 */

namespace core\db;


class DBQueryBuilder
{
    private $quury_parts = [
        "where" => [],
        "order" => [],
        "groupby"=>[],
        "having" => [],
        "limit" => null,
        "offset" => null,
        "join" => [],
        "fields" => [],
        "values" => [],
        "table" => null
    ];
    private $executor;
    private $class;
    const DEF_CONFIG_NAME = "films";

    public function __construct($name = self::DEF_CONFIG_NAME,$class=null)
    {
        $this->class=$class;
        $this->executor = DBExecutor::instance($name);
    }

    public static function create($name = self::DEF_CONFIG_NAME,$class=null){
        return new self($name,$class);
    }

    //Prepare fields, values and statement

    private static function _field($f)
    {
        return "`" . str_replace('.', '`.`', $f) . "`";
    }
    private static function _value($f)
    {
        return "\"" .$f. "\"";
    }
    private function isAggregate(string $field):bool{
        $pattern = '/.*\(.*\)/';
        return preg_match($pattern,$field);
    }

    //SELECT

    public function select(array $fields)
    {
        $this->quury_parts["fields"] = array_map(function ($f) {
            if ($this->isAggregate($f))return $f;
            else return self::_field($f);
        }, $fields);
        return $this;
    }

    public function from(string $table){
        $this->quury_parts["table"] = self::_field($table);
        return $this;
    }

    //WHERE

    private function _where($type,$field,$sign,$value,bool $native){
        if($value===null) {
            $value = $sign;
            $sign = "=";
        }
        if(!$native) $field = self::_field($field);
        if(!$native && $value[0]!="?" && $value[0]!=":" && !is_integer($value)) $value=$this->executor->quote($value);
        $this->quury_parts["where"][] = [$type,$field,$sign,$value];
    }

    public function where($field,$sign,$value=null,bool $native=false){
        $this->_where("",$field,$sign,$value,$native);
        return $this;
    }
    public function andWhere($field,$sign,$value=null,bool $native=false){
        $this->_where("AND",$field,$sign,$value,$native);
        return $this;
    }
    public function orWhere($field,$sign,$value=null,bool $native=false){
        $this->_where("OR",$field,$sign,$value,$native);
        return $this;
    }
    private function _groupWhere(callable $where,$type){
        if($type!=null) $this->quury_parts["where"][]=[$type];
        $this->quury_parts["where"][]=["("];
        $where($this);
        $this->quury_parts["where"][]=[")"];
        return $this;
    }
    public function whereGroup(callable $where){
        return $this->_groupWhere($where,null);
    }
    public function andWhereGroup(callable $where){
        return $this->_groupWhere($where,"AND");
    }
    public function orWhereGroup(callable $where){
        return $this->_groupWhere($where,"OR");
    }

    private function buildWhere(){
        $q="";
        if(!empty($this->quury_parts["where"])){
            $q.=" WHERE";
            foreach ($this->quury_parts["where"] as $w){
                $q.=" {$w[0]} ";
                if(count($w)>1) $q.="({$w[1]} {$w[2]} {$w[3]})";
            }
        }
        return $q;
    }

    //LIMIT and OFFSET

    public function limit(int $limit,int $offset=null){
        $this->quury_parts["limit"]=$limit;
        $this->quury_parts["offset"]=$offset;
        return $this;
    }
    private function buildLimit():string {
        $q="";
        if (!empty($this->quury_parts["limit"])){
            $q.=" LIMIT {$this->quury_parts["limit"]}";
            if (!is_null($this->quury_parts["offset"]))$q.=" OFFSET {$this->quury_parts["offset"]}";
        }
        return $q;
    }

    //ORDER BY

    public function orderBY(string $column,string $direction=null){
        $this->quury_parts["order"][]=$column;
        $this->quury_parts["order"][]=$direction;
        return $this;
    }
    private function buildOrderBy():string{
        $q="";
        if (!empty($this->quury_parts["order"])){
            $column = $this->quury_parts["order"][0];
            $direction = $this->quury_parts["order"][1];
            $q.=" ORDER BY {$column}";
            if(!is_null($direction))$q.=" {$direction}";
        }
        return $q;
    }

    //GROUP BY
    public function groupBy(array $fields){
        $this->quury_parts["groupby"]=implode(",",$fields);
        return $this;
    }
    private function buildGroupBy():string{
        $q="";
        if (!empty($this->quury_parts["groupby"])){
            $q.=" GROUP BY {$this->quury_parts["groupby"]}";
            //HAVING check
            if(!empty($this->quury_parts["having"])){
                $q.=" HAVING";
                foreach ($this->quury_parts["having"] as $h){
                    $q.=" {$h[0]} ";
                    if(count($h)>1) $q.="({$h[1]} {$h[2]} {$h[3]})";
                }
            }
        }
        return $q;
    }

    //HAVING
    private function _having($type,$field,$sign,$value,bool $native){
        if($value===null) {
            $value = $sign;
            $sign = "=";
        }
        if(!$native) $field = self::_field($field);
        if(!$native && $value[0]!="?" && $value[0]!=":" && !is_integer($value)) $value=$this->dbh->quote($value);
        $this->quury_parts["having"][] = [$type,$field,$sign,$value];
    }
    public function having($field,$sign,$value=null,bool $native=false){
        $this->_having("",$field,$sign,$value,$native);
        return $this;
    }
    public function andHaving($field,$sign,$value=null,bool $native=false){
        $this->_having("AND",$field,$sign,$value,$native);
        return $this;
    }
    public function orHaving($field,$sign,$value=null,bool $native=false){
        $this->_having("OR",$field,$sign,$value,$native);
        return $this;
    }
    private function _groupHaving(callable $where,$type){
        if($type!=null) $this->quury_parts["having"][]=[$type];
        $this->quury_parts["having"][]=["("];
        $where($this);
        $this->quury_parts["having"][]=[")"];
        return $this;
    }
    public function havingGroup(callable $having){
        return $this->_groupHaving($having,null);
    }
    public function andHavingGroup(callable $having){
        return $this->_groupHaving($having,"AND");
    }
    public function orHavingGroup(callable $having){
        return $this->_groupHaving($having,"OR");
    }

    private function buildSelect(){
        $fields = empty($this->quury_parts["fields"])?"*":implode(", ",$this->quury_parts["fields"]);
        $q = "SELECT {$fields} FROM {$this->quury_parts["table"]}";
        $q.=$this->buildJoin();
        $q.=$this->buildWhere();
        $q.=$this->buildGroupBy();
        $q.=$this->buildOrderBy();
        $q.=$this->buildLimit();
        return $q;
    }

    //JOIN

    private function buildJoin():string{
        $q="";
        if (!empty($this->quury_parts["join"])){
            $type = strtoupper($this->quury_parts["join"]["type"]);
            $table = $this->quury_parts["join"]["second_table"];
            $key1 = $this->quury_parts["join"]["key"][0];
            $key2 = $this->quury_parts["join"]["key"][1];
            $q.=" {$type} JOIN {$table} on {$this->quury_parts["table"]}.{$key1} = {$table}.{$key2}";
            //Join more tables
            if (!empty($this->quury_parts["join"]["addParams"])){
                foreach ($this->quury_parts["join"]["addParams"] as $addParam) {
                    $q.=" INNER JOIN {$addParam[0]} on 
                    {$addParam[0]}.{$addParam[1]} = {$addParam[2]}.{$addParam[3]}";
                }
            }
        }
        return $q;
    }

    public function join(string $type,string $table,array $key,array $addParams=[]){
        $this->quury_parts["join"]["type"]=$type;
        $this->quury_parts["join"]["second_table"]=self::_field($table);
        $this->quury_parts["join"]["key"]=$key;
        //Strictly arrays!!! ...["addParams"]=[[],[]]
        $this->quury_parts["join"]["addParams"]=[$addParams];
        return $this;
    }

    //ALL SELECT

    public function all($data=[]){
        return $this->executor->executeSelect($this->buildSelect(),$data);
    }

    public function get($data=[]){
        return array_map(function ($x){
            return new $this->class($x);
        },$this->all($data));
    }

    public function one($data=[]){
        return $this->executor->executeSelectOne($this->buildSelect(),$data);
    }
    public function first($data=[]){
        $q = $this->one($data);
        return $q ? new $this->class($q) : null;
    }

    //INSERT UPDATE

    public function insert($table,array $data){
        $fields = implode("`,`",array_keys($data));
        $values = implode(", :",array_keys($data));
        $q ="INSERT INTO `{$table}` (`{$fields}`) VALUES (:{$values})";
        return $this->executor->executeInsert($q,$data);
    }

    public function update($table,array $data,array $params=[]){
        $where = $this->buildWhere();
        $list = implode(",",array_map(function ($f){
            return "`{$f}`=:_param_$f";
        },array_keys($data)));

        $insert_data=[];
        foreach ($data as $k=>$v){
            $insert_data["_param_{$k}"]=$v;
        }
        $q = "UPDATE {$table} SET {$list} {$where}";

        $this->executor->executeUpdate($q,array_merge($insert_data,$params));
    }

}
