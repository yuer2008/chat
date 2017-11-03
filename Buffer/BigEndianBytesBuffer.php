<?php
namespace app\Buffer;
/**
 * PHP bytes array operation  API
 *
 * Copyright (c) 2010 sunli <sunli1223ATgmail.com>
 *
 * @version    $Id$
 * @author     sunli <sunli1223ATgmail.com>
 * @link       http://sunli.cnblogs.com
 */
use app\Buffer\BigEndianBuffer;
//require_once  dirname ( __FILE__ ) . '/BigEndianBuffer.php';
class BigEndianBytesBuffer extends BigEndianBuffer {
    private $bytes;
    private $readerIndex = 0;
    private $writeIndex = 0;

    //$bytes 初始化buffer
    public function __construct($bytes='') {
        $this->bytes = $bytes;
        $this->writeIndex += strlen ( $bytes );
    }
    //读len个byte(多字节读取)
    public function readBytes($len) {
        if ($len < 1) {
            return false;
        }
        $str = substr ( $this->bytes, $this->readerIndex, $len );
        $this->readerIndex += $len;
        return $str;
    }
    //读所有bytes(包含已读的bytes)
    public function readAllBytes() {
        return $this->bytes;
    }

    //读剩下bytes(不包含已读bytes)
    public function readRemainAllBytes()
    {
        $allLen = strlen($this->bytes);
        $remainLen = $allLen - $this->readerIndex;
        $str = substr ( $this->bytes, $this->readerIndex,$remainLen);
        $this->readerIndex += $remainLen;
        return $str;
    }
    //写bytes字节
    public function writeBytes($bytes) {
        $this->bytes .= $bytes;
        $this->writeIndex += strlen ( $bytes );
    }
    //清空整个bytes
    public function clear() {
        $this->bytes = null;
        $this->readerIndex = 0;
        $this->writeIndex = 0;
    }
}
?>