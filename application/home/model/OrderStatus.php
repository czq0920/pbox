<?php
namespace app\home\model;
use think\Model;
class OrderStatus extends Model{
    protected $table = 'order_status';
    protected $connection;
    public function __construct($data = [])
    {
    	parent::__construct($data);
    }
    
}
