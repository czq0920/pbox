<?php 
namespace app\common\tools;
/**
* 
*/


class Upload
{
	private $_max_size = 2*1024*1024;
	private $_ext_allow_list = array('.jpg','.png','.gif');
	private $_mime_allow_list = array('image/png','image/gif','image/jpeg','image/pjpeg','image/x-png');
	private $_root_dir = UPLOAD_PATH;
	private $_head_file_name = 'czq_';
	public function setMaxSize($max_size)
	{
		$this->_max_size = $max_size;
	}
	public function setAllowList($ext_allow_list)
	{
		$this->_ext_allow_list = $ext_allow_list;
	}
	public function setMineAllowList($mime_allow_list)
	{
		$this->_mime_allow_list = $mime_allow_list;
	}
	public function setRootDir($root_dir)
	{
		$this->_root_dir = $root_dir;
	}
	public function setHeadFileName($head_file_name)
	{
		$this->_head_file_name = $head_file_name;
	}
	public function doUpload(array $file_info)
	{
			$file_tmp_name = $file_info['tmp_name'];
			$ext = strtolower(strrchr($file_info['name'], '.'));				
				if($file_info['error']!==0)
				{
					echo "上传文件错误！";
					return false;
				}
					
				if($file_info['size']>$this->_max_size)
				{
					echo "上传文件大于2M！";
					return false;
				}
				if(!in_array($ext, $this->_ext_allow_list))
				{
					echo '文件类型非法！';
					return false;
				}
				$finfo = new \Finfo(FILEINFO_MIME_TYPE);
				$mime_type = $finfo->file($file_tmp_name);
				if(!in_array($mime_type,$this->_mime_allow_list))
				{
					echo '<br> 文件mime类型错误error3';
					return false;
				}		
				$head_dir = $this->_root_dir.date('Ymd').'/';
				if(!is_dir($head_dir))
				{
					if(!mkdir($head_dir,0777,true))
					{
						echo '创建文件目录失败！';
						return false;
					}
				}
				//$file_name = date('YmdHis').uniqid().$ext;
				$file_name = uniqid().$ext;
				//$dir = str_replace('\\','/',getcwd());
			
				$write_file_dir = $head_dir.$this->_head_file_name.$file_name;
				if(move_uploaded_file($file_tmp_name,$write_file_dir))
				{
					//echo '上传成功！';
					return $write_file_dir;
				}
				else
				{
					echo '上传失败！';
					return false;
				}				
	}
}



 ?>