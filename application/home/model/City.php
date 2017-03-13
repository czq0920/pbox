<?php
namespace app\home\model;
use think\Model;
class City extends Model{
    protected $table = 'city';
    protected $connection;
    public function __construct($data = [])
    {
    	parent::__construct($data);
    }
    
}
