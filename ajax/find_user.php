<?php
	$result = array("username" => $_GET["username"], "founded" => true);
	error_reporting(0);
	try{
		//tentando carregar o código fonte da página do usuário
		if (!$page = fopen("http://web.stagram.com/n/".$_GET["username"]."/?vm=grid", "rb")){
			throw new Exception('Error');
		}
		else{
			$contents = '';
			//armazenando o código fonte do usuário em uma string
			while (!feof($page))
				$contents .= fread($page, 9999999);
			$result["founded"] = true;
			
			//obtendo o título da página (nome @nome_de_usuario)
			$title = substr($contents, strpos($contents, "<title>") + 8, strpos($contents, "&#039;s") - strpos($contents, "<title>") - 8);
			
			$result["name"] = str_replace("@".$_GET["username"], "", str_replace("(", "", str_replace(")", "", $title)));
			
			//obtendo a descrição do perfil do usuário
			$description = substr($contents, strpos($contents, '<div style="margin-left:300px">') + 31, 99999);
			$description = substr($description, 0, strpos($description, "</div>"));
			$result["description"] = $description;
			
			//obtendo a imagem de exibição do usuário
			$thumb = substr($contents, strpos($contents, '<div class="profimage_small">') + 29, 9999);
			$thumb = substr($thumb, 0, strpos($thumb, "</div>"));
			$result["thumb"] = str_replace('<a href="/n/'.$_GET["username"].'/">', "", str_replace("</a>", "", $thumb));
			
			//obtendo os números de fotos...
			$numbers = substr($contents, strpos($contents, '<td style="text-align:center;"><span style="font-size:123.1%;">') + 63, 9999);
			$result["numbers"]["photos"] = substr($numbers, 0, strpos($numbers, "</span>"));
			//...número de seguidores...
			$numbers = substr($numbers, strpos($numbers, '<td style="text-align:center;"><span style="font-size:123.1%;"') + 61, 9999);
			$numbers = substr($numbers, strpos($numbers, '">') + 2, 9999);
			$result["numbers"]["followers"] = substr($numbers, 0, strpos($numbers, "</span>"));
			//...número de seguindo...
			$numbers = substr($numbers, strpos($numbers, '<td style="text-align:center;"><span style="font-size:123.1%;"') + 61, 9999);
			$numbers = substr($numbers, strpos($numbers, '">') + 2, 9999);
			$result["numbers"]["following"] = substr($numbers, 0, strpos($numbers, "</span>"));
			
			//obtendo informações das fotos
			$pos = 0;
			while (strpos($contents, "_6.jpg", $pos)){
				$i = 0;
				while (substr($contents, strpos($contents, "_6.jpg", $pos) - $i, 4) != "http")
					$i++;
				$j = 0;
				while (substr($contents, strpos($contents, "_6.jpg", $pos) - $j, 6) != 'href="')
					$j++;
				$link = "";
				$a = 0;
				while ($contents[strpos($contents, "_6.jpg", $pos) - $j + 9 + $a] != '"'){
					$link .= $contents[strpos($contents, "_6.jpg", $pos) - $j + 9 + $a];
					$a++;
				}
				
				//obtendo a legenda da foto
				if (strpos($contents, '<div id="photo_caption_'.$link.'" style="display:none;">')){
					$title = substr($contents, strpos($contents, '<div id="photo_caption_'.$link.'" style="display:none;">') + strlen($link) + 47, strpos($contents, "</div>", strpos($contents, '<div id="photo_caption_'.$link.'" style="display:none;">')) - strpos($contents, '<div id="photo_caption_'.$link.'" style="display:none;">') - strlen($link) - 47);
					$title = str_replace("/'", "'", str_replace('/"', '"', str_replace('href="/n/', 'href="?user=', str_replace("href='/tag/", "onclick='return false' href='#", $title))));
				}
				else
					$title = "";
				//obtendo a legenda sem as tags <a>
				$title_textplain = preg_replace("/\<a(.*)\>(.*)\<\/a\>/iU", "$2", $title);
				//número de likes da foto
				$likes = explode("<", substr($contents, strpos($contents, '<span class="likes"><span>', strpos($contents, "_6.jpg", $pos) + 1) + 26, 6));
				$likes = $likes[0];
				//número de comentários da foto
				$comments = explode("<", substr($contents, strpos($contents, '<span class="comments"><span>', strpos($contents, "_6.jpg", $pos) + 1) + 29, 6));
				$comments = $comments[0];
				//tempo de postagem da foto
				$time_ago = explode(" ago", preg_replace("/\<a(.*)\>(.*)\<\/a\>/iU", "$2", substr($contents, strpos($contents, '<span class="posted_time">', strpos($contents, "_6.jpg", $pos) + 1) + 26, 300)));
				$time_ago = $time_ago[0];
				//separando a parte numérica da unidade de tempo
				foreach (range(0, strlen($time_ago)) as $letter){
					if (!is_numeric($time_ago[$letter])){
						$time = array(substr($time_ago, 0, $letter), substr($time_ago, $letter, 100));
						break;
					}
				}
				//traduzindo as unidades de tempo
				switch ($time[1]){
					case "s": $unit = $time[0] == 1 ? "segundo" : "segundos"; break;
					case "sec": $unit = $time[0] == 1 ? "segundo" : "segundos"; break;
					case "min": $unit = $time[0] == 1 ? "minuto" : "minutos"; break;
					case "h": $unit = $time[0] == 1 ? "hora" : "horas"; break;
					case "d": $unit = $time[0] == 1 ? "dia" : "dias"; break;
					case "w": $unit = $time[0] == 1 ? "semana" : "semanas"; break;
					case "mon": $unit = $time[0] == 1 ? "mês" : "meses"; break;
					case "y": $unit = $time[0] == 1 ? "ano" : "anos"; break;
					default: $unit = $time[1];	
				}
				$time = $time[0]." ".$unit." atrás";
				//armazenando as infos da foto e as urls do links
				if ($likes != 'ing="U') //comparação demoníaca
					$result["photos"][] = array("small" => substr($contents, strpos($contents, "_6.jpg", $pos) - $i, $i)."_5.jpg", "mid" => substr($contents, strpos($contents, "_6.jpg", $pos) - $i, $i)."_6.jpg", "big" => substr($contents, strpos($contents, "_6.jpg", $pos) - $i, $i)."_7.jpg", "photo_id" => $link, "title" => $title, "title_textplain" => $title_textplain, "likes" => $likes, "comments" => $comments, "time" => $time);
				$pos = strpos($contents, "_6.jpg", $pos) + 1;
			}
			
			//verificando se existe uma próxima página para ser lida
			if (strpos($contents, 'rel="next"')){
				$i = 0;
				while (substr($contents, strpos($contents, '" rel="next"') - $i, 6) != 'href="')
					$i++;
				$result["next_page"] = substr($contents, strpos($contents, '" rel="next"') - $i + strlen('href="/n/'.$result["username"].'/"'), $i - strlen('href="/n/'.$result["username"].'/"'));
				$result["more_photos"] = true;
			}
			else
				$result["more_photos"] = false;
		}
		fclose($page);
	}
	catch (Exception $e){
		$result["founded"] = false;
	}
	echo json_encode($result);
?>