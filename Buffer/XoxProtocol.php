<?php
namespace app\Buffer;

class XoxProtocol
{
    public $len;
    public $cmd;
    public $flag;
    public $body;

    //编码
    public function encode()
    {
        $this->len = strlen($this->body)+1;
        $this->flag = 0;
        $old = new BigEndianBytesBuffer($this->body);
        $len = $this->bodyFlag($this->body);
        $old->writeUnsignedChar($len & 0x000f);//取长度的低８位
        $this->body = $old->readAllBytes();
    }
    //解码

    private function bodyFlag($body)
    {
        $len = strlen($body);
        $flag = 0;
        for($i=0;$i<$len;$i++)
        {
            $flag += ord($body[$i]);
        }
        return $flag;
    }
}