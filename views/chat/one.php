<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>chat room</title>
	<meta name="description" content="char"/>
	<meta name="keywords" content=""/>
	<meta name="viewport"
      content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
	<link href="static/css/main.css" rel="stylesheet">
</head>
<body>
	<div id="login_form" style="display:none;">
		<input type="text" name="name" value="yangfutao"><br />
		<input type="password" name="pwd" value="123123"><br />
		<input type="button" class="enter_event" value="submit" id="submit">
	</div>
	<div id="main">
		<div id="nav">
			<ul><li><span id="login_btn" class="hand blue">登录</span></li>
				<li id="logout_btn"><span id="user_name"></span><a href="javascript:void(0)" id="logout">退出</a>
				<input type="hidden" id="uid" value="">
				</li>
			</ul>
		</div>
		<div id="room_info clear">
			当前在线人数：<span id="user_count">0</span>
		</div>
		<div id="content_list">
			
		</div>
		<div id="online_list">
			<p>当前用户:</p>
			<ul>
				<li>11</li>
				<li>33</li>
			</ul>
		</div>
		<div id="input_wrap">
			<div><textarea id="content"></textarea></div>
			<input type="button" class="enter_event" id="send" value="send">
		</div>
	</div>
	<script src="static/js/jquery1.8.3.js"></script>
	<script src="static/js/layer-v2.1/layer.js"></script>
	<script src="static/js/comm.js"></script>
	<script type="text/javascript">
	$('#content').focus();
	//open socket
	try{
		var ws = new WebSocket('ws://localhost:9110');	
	}catch(e){
		alert(e.message)
	}
	
	ws.onopen = function(event){
		ws.send('hello ,this is yang');
	};
	ws.onerror = function(event){
		
	}
	ws.onmessage = function(msg){
		//receive msg from server
		var data = msg.data;

		if(data == '')return;
		try{
			data = JSON.parse(data);	
		}catch(e){
			//alert(e.message);
		}
		// console.dir(data)
		if(data['type'] == 201){// room user count
			if(data['code'] == 1){
				$('#user_count').html(data['data']['count']);
				//online user list
				if(data['data']['online_user_list'].length > 0){
					var str = '';
					for(i in data['data']['online_user_list']){
						str += '<li>' +data['data']['online_user_list'][i]+ '</li>';
					}
					$('#online_list ul').html(str);
				}
			}
			
		}else if(data['type'] == 100){// login
			if(data['code'] == 1){
				$('#user_name').html(data['data']['user_name']);
				$('#uid').val(data['data']['uid']);
				$('#login_btn').parent('li').hide();
				$('#logout_btn').show();
			}
		}else if(data['type'] == 301){ //receive chat message
			if(data['code'] == 1){
				$('#content_list').append('<div class="content_item">'+data['data']['msg'] + '</div>');
				//scroll to bottom
				$('#content_list').get(0).scrollTop = 99999;
			}
		}

		
		
	}
	//send message
	$('#send').on('click', function(){
		var content =$('#content').val();
		var uid = $('#uid').val();
		var touid = $('#touid').val();
		if('' == uid){
			layer.msg('请先登录',{icon:0});
			return false;
		}
		if('' != content){
			var data = JSON.stringify({type:301,data:{touid:touid,message:content,uid:uid}});
			ws.send(data);
			$('#content').val('');	
		}
		
	});
	//login 
	$('#login_btn').on('click', function(){
		var lay1 = layer.open({
			type: 1,
			area : ['500px', '300px'],
			content:$('#login_form')
		});
		$('input[name="name"]').focus();
		$('#submit').on('click', function(){
			var name = $('input[name="name"]').val();
			var pwd = $('input[name="pwd"]').val();
			
			var data = JSON.stringify({type:100,data:{name:name, pwd:pwd}});
			ws.send(data);
			layer.close(lay1);
		});
	});
	
	</script>
</body>
</html>    