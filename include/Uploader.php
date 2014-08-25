<?php
class Uploader {
	public function __construct() {

	}
	/**
	 * return :返回数组成功 2.文件类型错误 3.文件太大 4.5.保存出错
	 */
	public function uploadImg($savePath, $saveName, $allowedExts, $allowedSize) {
		$temp = explode(".", $_FILES["file"]["name"]);
		$extension = end($temp);
		$saveName = $saveName . "." . $extension;
		//文件类型
		if (!(($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png"))) {
			return "2";
		}
		if (!in_array($extension, $allowedExts)) {
			return "2";
		}
		//文件大小
		$size = $_FILES["file"]["size"] / 1024 / 1024; //M
		if ($size > $allowedSize) {
			return "3";
		}
		if ($_FILES["file"]["error"] > 0) {
			echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
			return "4";
		}
		//保存文件
		if (move_uploaded_file($_FILES["file"]["tmp_name"], $savePath . $saveName)) {
			$ret = array (
				'filePath' => $savePath,
				'fileName' => $saveName,
				'fileSize' => $size
			);
			return $ret;
		} else {
			return "5";
		}
	}
}
?>
