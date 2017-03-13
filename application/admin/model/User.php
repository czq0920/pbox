<?php
namespace app\admin\model;
use think\Model;
class User extends Model{
    protected $table = 'user';
    protected $connection;
    public function __construct($data = [])
    {
    	parent::__construct($data);
    }
    
}
