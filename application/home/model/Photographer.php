<?php
namespace app\home\model;
use think\Model;
class Photographer extends Model{
    protected $table = 'photographer';
    protected $connection;
    public function __construct($data = [])
    {
    	parent::__construct($data);
    }
    
}
