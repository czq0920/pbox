<?php 
namespace framework\library;
class Page
{
	private $_pagesize = 3;//每页显示的记录数
	private $_now_page=1;//现在的页码
	private $_total=5;//总的记录数
	private $_pagenum=5;//分成多少页
	//private $_index_page=1;//biao
	private $_url = 'index.php';//脚本的url
	private $_paras=array();//a传的参数
	private $_first=1;	    // 首页
	private $_last=1;	    // 末页
	private $_prev=1;	    // 上一页
	private $_next=1;	    // 下一页
	public function __set($pro,$val)
	{
		 if(property_exists($this,$pro))
		 {
		 	$this->$pro = $val;
		 }
		 else
		 {
		 	echo '属性名不存在！';
		 }

	}
	public function __get($pro)
	{
		//echo $pro; die;
		if(property_exists($this,$pro))
		 {

		 	return $this->$pro;
		 }
		 else
		 {
		 	echo '属性名不存在！';
		 }
	}
	public function create()
	{	
		$page = isset($_GET['page'])?$_GET['page']:$this->_now_page;		
		$this->_now_page = $page;
		$this->_last = ceil($this->_total/$this->_pagesize);
		//$index_page_pre = $this->_index_page==1?1:$this->_index_page-1;
		//$index_page_next = $this->_index_page==$this->_last?$this->_last:$this->_index_page+1;
		$this->_prev = $this->_now_page==1?1:$this->_now_page-1;
		$this->_next = $this->_now_page==$this->_last?$this->_last:$this->_now_page+1;
		$tmp =array();
		foreach ($this->_paras as $para_key => $para_val) {
			$tmp[] = "$para_key = $para_val";
		}
		$this->_url = $this->_url.'?'.implode('&', $tmp).'&page=';
$page_nav_html = <<<PAGEHTML
		<ul class="pagination">
				<li>
				    <a aria-label="First" href="$this->_url$this->_first">
				        <span aria-hidden="true">首页</span>
				    </a>
				</li>
PAGEHTML;

		$prev_disabled = $this->_now_page == 1?'disabled':'';		
$page_nav_html .= <<<PAGEHTML
		
				<li class="$prev_disabled">
				    <a aria-label="prev" href="$this->_url$this->_prev">
				        <span aria-hidden="true">上一页</span>
				    </a>
				</li>
PAGEHTML;
		for($i=$this->_now_page;$i<$this->_now_page+$this->_pagenum;$i++)
		{
			if($i <$this->_first||$i >$this->_last)
			{
				continue;
			}
		$class = $i==$this->_now_page?'active':'';

$page_nav_html .= <<<PAGEHTML
				<li class="$class">
					  <a href="$this->_url$i">$i</a>
				</li>
PAGEHTML;
		}
		$disabled = $this->_now_page == $this->_last?'disabled':'';
$page_nav_html .= <<<PAGEHTML
		
				<li class="$disabled">
				    <a aria-label="next" href="$this->_url$this->_next">
				        <span aria-hidden="true">下一页</span>
				    </a>
				</li>
PAGEHTML;
$page_nav_html .= <<<PAGEHTML
				<li>
				    <a aria-label="End" href="$this->_url$this->_last">
				        <span aria-hidden="true">尾页</span>
				    </a>
				</li>
		</ul>
PAGEHTML;
	return 	$page_nav_html;
	}
}


 ?>