<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Instagram Photo Downloader</title>
<link href="http://fonts.googleapis.com/css?family=Abel|Arvo" rel="stylesheet" type="text/css" />
<link href="style.css" rel="stylesheet" type="text/css" media="screen" />
<link href="lib/jquery.lightbox.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="lib/jquery.js"></script>
<script type="text/javascript" src="lib/jquery.lightbox.js"></script>
<script>
	function search_username(user){
		if (user != null)
			$("#search_username").attr("value", user)
		if ($("#search_username").val().length > 0){
			window.history.pushState("object or string", "Title", "/instagram_photo_downloader/?user=" + $("#search_username").val())
			$("#loader").fadeIn("slow").css("display", "block")
			$("#result").slideUp("fast")
			$("#error").slideUp("fast")
			$.ajax({
				url: "ajax/find_user.php",
				method: "get",
				data: "username=" + $("#search_username").val(),
				dataType: "json",
				success: function(data){
					$("#loader").fadeOut("slow")
					if (data.founded){
						$("#result .name").html(data.name + " @" +data.username)
						$("#result .description").html(data.description)
						$("#result .thumb").html(data.thumb)
						$("#result .numbers").html(data.numbers.photos + " fotos | " + data.numbers.followers + " seguidores | seguindo " + data.numbers.following)
						$("#result .photos_list").html('<li style="height: 25px"><div style="margin-top: 0px" class="checkbox on" id="main_checkbox" onclick="toggle_all_checkboxes()"><input type="hidden" class="fake_checkbox" id="main_fake_checkbox" value="1"></div> <div id="loader_photos" style="position: absolute; margin-left: 47px; display: none"><img src="images/loader_circle.gif" style="vertical-align: middle"> &nbsp;Carregando fotos...</div> <div id="progress" style="margin-left: 480px; position: absolute; margin-top: 4px; display: none"></div> <div style="margin-top: 4px; margin-left: 620px;"><a id="download_button" href="javascript: void()" class="button" onclick="process_download(\'' + data.username + '\', 0); return false" style="box-shadow: 2px 2px 6px #AAA; display: none"><img src="images/download.png" border="0" style="vertical-align: middle" /> Salvar fotos selecionadas</a></div></li>')
						for (i=0; i<data.photos.length; i++){
							rand = (Math.random() + "").replace(".", "")
							$("#result .photos_list").append('<li id="' + data.photos[i].photo_id + '"><div class="checkbox on" id="checkbox_' + rand + '" onclick="toggle_checkbox(\'' + rand + '\')"><input type="hidden" id="download_link_' + rand + '" value="' + data.photos[i].big + '"><input type="hidden" class="fake_checkbox" id="fake_checkbox_' + rand + '" value="1"></div><img style="vertical-align: top; margin-left: 40px" src="' + data.photos[i].small + '" width="40" height="40"><span class="information"><span><div class="title">' + data.photos[i].title + '</div><div class="link"><span title="' + (data.photos[i].likes == 0 ? "Ninguém curtiu" : (data.photos[i].likes == 1 ? "1 curtiu" : data.photos[i].likes+" curtiram")) + '"><img src="images/likes.png" style="vertical-align: middle"> ' + data.photos[i].likes + '</span> &nbsp; <span title="' + (data.photos[i].comments == 0 ? "Nenhum comentário" : (data.photos[i].comments == 1 ? "1 comentário" : data.photos[i].comments+" comentários")) + '"><img src="images/comments.png" style="vertical-align: middle"> ' + data.photos[i].comments + '</span> &nbsp; &nbsp; <a href="' + data.photos[i].small + '" target="_blanc" style="font-size: 12px" class="small_size" alt="' + data.photos[i].title_textplain + '">[pequeno]</a> <a href="' + data.photos[i].mid + '" target="_blanc" style="font-size: 14px" class="mid_size" alt="' + data.photos[i].title_textplain + '">[médio]</a> <a href="' + data.photos[i].big + '" target="_blanc" style="font-size: 16px" class="big_size" alt="' + data.photos[i].title_textplain + '">[grande]</a><span class="time_ago">' + data.photos[i].time + '</span></div></span><div class="cover"></div></span></li>')
						}
						$(".small_size").lightBox()
						$(".mid_size").lightBox()
						$(".big_size").lightBox()
						if (data.more_photos){
							$("#result .photos_list").append('<li class="more_photos_loader"><img src="images/loader.gif" /><br />Carregando mais fotos</li>')
							$("#loader_photos").fadeIn("slow")
							load_more_photos(data.username, data.next_page)
						}
						else
							$("#download_button").css("display", "")
						$("#result").slideDown("fast")
					}
					else{
						$("#error").slideDown("fast")
					}
				}
			})
		}
	}
	
	function load_more_photos(username, next_page){
		$.ajax({
			url: "ajax/load_more_photos.php",
			method: "get",
			data: "username=" + username + "&next_page=" + next_page,
			dataType: "json",
			success: function(data){
				for (i=0; i<data.photos.length; i++){
					rand = (Math.random() + "").replace(".", "")
					$("#result .more_photos_loader").before('<li id="' + data.photos[i].photo_id + '"><div class="checkbox on" id="checkbox_' + rand + '" onclick="toggle_checkbox(\'' + rand + '\')"><input type="hidden" id="download_link_' + rand + '" value="' + data.photos[i].big + '"><input type="hidden" class="fake_checkbox" id="fake_checkbox_' + rand + '" value="1"></div><img style="vertical-align: top; margin-left: 40px" src="' + data.photos[i].small + '" width="40" height="40"><span class="information"><span><div class="title">' + data.photos[i].title + '</div><div class="link"><span title="' + (data.photos[i].likes == 0 ? "Ninguém curtiu" : (data.photos[i].likes == 1 ? "1 curtiu" : data.photos[i].likes+" curtiram")) + '"><img src="images/likes.png" style="vertical-align: middle"> ' + data.photos[i].likes + '</span> &nbsp; <span title="' + (data.photos[i].comments == 0 ? "Nenhum comentário" : (data.photos[i].comments == 1 ? "1 comentário" : data.photos[i].comments+" comentários")) + '"><img src="images/comments.png" style="vertical-align: middle"> ' + data.photos[i].comments + '</span> &nbsp; &nbsp; <a href="' + data.photos[i].small + '" target="_blanc" style="font-size: 12px" class="small_size" alt="' + data.photos[i].title_textplain + '">[pequeno]</a> <a href="' + data.photos[i].mid + '" target="_blanc" style="font-size: 14px" class="mid_size" alt="' + data.photos[i].title_textplain + '">[médio]</a> <a href="' + data.photos[i].big + '" target="_blanc" style="font-size: 16px" class="big_size" alt="' + data.photos[i].title_textplain + '">[grande]</a><span class="time_ago">' + data.photos[i].time + '</span></div></span><div class="cover"></div></span></li>')
				}
				$(".small_size").lightBox()
				$(".mid_size").lightBox()
				$(".big_size").lightBox()
				if (data.more_photos)
					load_more_photos(data.username, data.next_page)
				else{
					$(".more_photos_loader").remove()
					$("#loader_photos").fadeOut("slow")
					$("#download_button").fadeIn("slow")
				}
			}
		})
	}
	
	function process_download(username, index, parameters){
		$("#download_button").removeClass("button").addClass("disabled_button")
		if (parameters == null){
			parameters = "username=" + username + "&index=" + index
			//verificando as fotos selecionadas
			$("input[id^=fake_checkbox_][value=1]").each(function(){
				parameters += "&url[]=" + $(this).prev().val()
			})
		}
		if (parameters != "username=" + username + "&index=" + index){
			if (index == 0){
				$("#progress").html("Progresso: iniciando").fadeIn("slow")
			}
			$.ajax({
				url: "ajax/process_download.php",
				method: "get",
				data: parameters,
				dataType: "json",
				success: function(data){
					//se houverem fotos a serem baixadas, é feito o download
					if (data.index <= data.total){
						$("#progress").html("Progresso: " + data.index + "/" + (data.total + 1))
						process_download(data.username, data.index, data.parameters)
					}
					//senão o arquivo zip é enviado para o browser
					else{
						$("#download_button").removeClass("disabled_button").addClass("button")
						$("#progress").html("Progresso: pronto!").delay(3000).fadeOut("slow")
						window.location.replace(data.file)
					}
				}
			})
		}
	}
	
	function toggle_checkbox(rand){
		val = $("#fake_checkbox_" + rand).val()
		if (val == 1){
			$("#checkbox_" + rand).removeClass("on").addClass("off")
			$("#fake_checkbox_" + rand).attr("value", "0")
		}
		else{
			$("#checkbox_" + rand).removeClass("off").addClass("on")
			$("#fake_checkbox_" + rand).attr("value", "1")
		}
	}
	
	function toggle_all_checkboxes(){
		val = $("#main_fake_checkbox").val()
		if (val == 1){
			$(".checkbox").removeClass("on").addClass("off")
			$(".fake_checkbox").attr("value", "0")
		}
		else{
			$(".checkbox").removeClass("off").addClass("on")
			$(".fake_checkbox").attr("value", "1")
		}
	}
	
	$(document).ready(function(){
		$(".search .button").click(function(){
			search_username()
		})
		<?php if ($_GET && trim($_GET["user"]) != ""){ ?>
			$("#search_username").attr("value", "<?php echo $_GET["user"] ?>")
			search_username("<?php echo $_GET["user"] ?>")
		<?php } ?>
		$('<img/>').attr('src', 'images/loader_circle.gif')
	})
	
	function enter(evt){
		var key_code = evt.keyCode  ? evt.keyCode  :
					   evt.charCode ? evt.charCode :
					   evt.which    ? evt.which    : void 0;
		return key_code == 13;
	}
</script>
</head>
<body>
<div id="wrapper">
	<div id="header-wrapper">
		<div id="header">
			<div id="logo">
				<h1><a href="/instagram_photo_downloader">Instagram<span>Photo</span>Downloader</a></h1>
			</div>
		</div>
	</div>
	<div id="page">
		<div class="post">
			<h2 class="title" align="center">Nome de usuário no Instagram</h2>
            <div align="center">
	            <div class="search">
                	<input type="text" autofocus class="textfield" id="search_username" onkeypress="if (enter(event)) search_username()" />
                    <a href="javascript: void()" class="button" onclick="return false"><img src="images/lupe.png" border="0" style="vertical-align: middle" /> Buscar</a>
                    <img src="images/loader.gif" id="loader" />
                </div>
            </div>
		</div>
        
        <div id="error">
        	<img src="images/user_not_found.png" />Usuário não encontrado ou sua conta é protegida
        </div>
        
        <form name="resultForm">
            <div id="result" style="display: none">
                <div class="post">
                    <table width="100%">
                        <tr>
                            <td width="80" class="thumb" valign="top" style="padding-top: 16px"></td>
                            <td valign="top">
                                <h2 class="name"></h2>
                                <h3 class="numbers"></h3>
                                <div class="description"></div>
                            </td>
                        </tr>
                    </table>
                    <ul class="photos_list">
                    </ul>
                </div>
            </div>
        </form>
    </div>
<div id="footer">
	<p>
    	Criado por <a href="mailto:etcholeite@gmail.com">Everton Leite</a>
    </p>
</div>
</body>
</html>