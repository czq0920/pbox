<?php
	//doDownload.php 完成下载任务.
	//1. 首先获取到要下载的文件的名字，并处理中文
	$file_name = isset($_GET['filename']) ? $_GET['filename'] : '';
	$file_info_name = isset($_GET['file_info_name']) ? $_GET['file_info_name'] : $file_name;
	if($file_name == ''){
		echo '你没有传递文件名，无法下载!';
		return;
	}
	
	//2.处理一下文件名的编码 utf-8 ==> gbk
	$file_name = iconv('utf-8', 'gbk' , $file_name);

	//3.找到文件的全路径
	define('DOWNLOAD_PATH', __DIR__ . '/');
	//4. 拼接文件的全路径
	//$file_full_path = DOWNLOAD_PATH . 'downfile/'  . $file_name;
	$file_full_path = DOWNLOAD_PATH. $file_name;
	// echo $file_full_path;
	// exit;
	

	if(!file_exists($file_full_path)){
		echo '文件都没有，无法下载';
		return;
	}

	//5. 判断文件如果过大，也不提供下载
	$file_size = filesize($file_full_path);
	if($file_size > 2 * 1024 * 1024){
		echo '文件过大，不提供下载';
		return;
	}

	//6. 这里我们为了让浏览器知道是下载文件，需要自己设置http响应头
	header("Content-type: application/octet-stream");
	//按照字节大小返回
	header("Accept-Ranges: bytes");
	//返回文件大小
	header("Accept-Length: $file_size");
	//这里客户端的弹出对话框，对应的文件名
	header("Content-Disposition: attachment; filename=".$file_info_name);

	//7. 打开文件，读取数据，返回数据给浏览器
	$fp = fopen($file_full_path, 'r');

	//循环读, 一次读取 1024;
	$buffer = 1024;
	while(!feof($fp)){
		$fdata = fread($fp, $buffer);
		//将读取到的数据返回给浏览器
		echo $fdata;
	}

	//关闭文件
	fclose($fp);


