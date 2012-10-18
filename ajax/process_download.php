<?php
	$result = array("username" => $_GET["username"], "index" => $_GET["index"] + 1, "total" => count($_GET["url"]) - 1);
	error_reporting(0);
	
	$filename = explode("/", $_GET["url"][$_GET["index"]]);
	$filename = $filename[count($filename) - 1];
	//baixando a imagem passada por parâmetro
	file_put_contents('../tmp/'.$filename, file_get_contents($_GET["url"][$_GET["index"]]));
	
	$parameters = "&username=".$result["username"]."&index=".$result["index"];
	foreach ($_GET["url"] as $url)
		$parameters .= "&url[]=".$url;
	$result["parameters"] = $parameters;
	
	//gerando o arquivo zip caso não hajam mais fotos a serem baixadas
	if ($_GET["index"] == count($_GET["url"]) - 1){
		$zip = new ZipArchive();
		$filename = "../tmp/".$_GET["username"].date("YmdHis").".zip";
		$zip -> open($filename, ZIPARCHIVE::CREATE);
		foreach ($_GET["url"] as $url){
			$file = explode("/", $url);
			$file = $file[count($file) - 1];
			$zip -> addFile("../tmp/".$file, $file);
		}
		$zip -> close();
		$result["file"] = str_replace("../", "", $filename);
	}

	echo json_encode($result);
?>