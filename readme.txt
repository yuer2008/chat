聊天室 demo 2017/7/17
首页：
http://domain/index.php?r=chat/one
==================
server端：
workerman 3.1
ip port 设置 config/config.php (localhost 9110)
==================
db: chatroom
chat_
用户表 ： member
==================
通信协议号
100  登录验证结果
101  退出登录
201  在线人数结果;在线用户列表
202  uid与连接绑定
301  发送消息
500  进入房间 ，加入group
600  系统消息
=================
技术实现：
1. 在线用户:
用户列表存放到redis集合

=================
问题：
1. 在worker的onconnect回调中 ，如果有向客户端发送数据 ，那么客户端连接失败(协议 websocket)

=============other ====
php use function

做chat room ，聊天窗口显示聊天内容，
1. 设置聊天窗口
<div id="content_list"></div> ，为了固定内容显示到范围内，需要出现滚动条 ，设置css
#content_list{
	border: 1px solid #949494;
	width: 500px;
	height: 500px;
	overflow-y:scroll; 
	overflow-x:scroll;
}
2.自动滚动到底部



			
		
