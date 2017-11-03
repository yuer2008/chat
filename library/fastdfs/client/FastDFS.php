<?php
/**
 * 此类实现了fastdfs的客户端，没有装扩展的情况下可以使用
 */
if (!class_exists('FastDFS', false)) {
    define('FDFS_PROTO_PKG_LEN_SIZE', 8);
    define('FDFS_PROTO_CMD_ACTIVE_TEST', 111);
    define('FDFS_PROTO_CMD_RESP', 100);
    define('FDFS_PROTO_CMD_UPLOAD_SLAVE_FILE', 21);
    define('FDFS_PROTO_CMD_DELETE_FILE', 12);
    define('FDFS_PROTO_CMD_GET_METADATA', 15);
    define('FDFS_PROTO_CMD_SET_METADATA', 13);

    //body_length + command + status
    define('FDFS_HEADER_LENGTH', 10);
    define('FDFS_IP_ADDRESS_SIZE', 16);
    define('FDFS_FILE_EXT_NAME_MAX_LEN', 6);
    define('FDFS_GROUP_NAME_MAX_LEN', 16);
    define('FDFS_OVERWRITE_METADATA', 1);
    define('FDFS_MERGE_METADATA', 2);

    // 连接超时时间
    define('FDFS_CONNECT_TIME_OUT', 5);
    define('FDFS_FILE_PREFIX_MAX_LEN', 16);

    //传输超时时间
    define('FDFS_TRANSFER_TIME_OUT', 0);
    define('FDFS_QUERY_STORE_WITHOUT_GROUP_ONE', 101);
    define('FDFS_QUERY_STORE_WITH_GROUP_ONE', 104);

    define('FDFS_TRACKER_QUERY_STORAGE_STORE_BODY_LEN', FDFS_GROUP_NAME_MAX_LEN + FDFS_IP_ADDRESS_SIZE + FDFS_PROTO_PKG_LEN_SIZE);

    class FastDFS {

        public $gConfig = array();

        /**
         *
         * @var FastDFSTrackerClient
         */
        private $tracker;

        /**
         *
         * @var FastDFSStorageClient
         */
        private $storage;
        private $error = array(
            'code' => 0,
            'msg' => ''
        );

        /**
         * 要使用这个类，你一定要在php的ini文件中进行fastdfs的配置
         * 
         * @throws FastDFSException
         */
        public function __construct() {
            $configFile = '';
            $ini = parse_ini_file(php_ini_loaded_file());

            if (!isset($ini['fastdfs_client.tracker_group_count'])) {
                throw new FastDFSException("no define fastdfs config");
            }
            for ($i = 0; $i < $ini['fastdfs_client.tracker_group_count']; $i++) {
                if (isset($ini['fastdfs_client.tracker_group' . $i])) {
                    $configFile = $ini['fastdfs_client.tracker_group' . $i];
                    break;
                }
            }
            if (!file_exists($configFile)) {
                throw new FastDFSException("client config $configFile not found");
            }
            $this->gConfig = parse_ini_file($configFile);
            list($this->gConfig['tracker_host'], $this->gConfig['tracker_port']) = explode(':', $this->gConfig['tracker_server']);
        }

        /**
         * 获得一个tracker
         * 
         * @return \FastDFSTrackerClient
         */
        public function tracker_get_connection() {
            $this->tracker = new FastDFSTrackerClient($this, $this->gConfig['tracker_host'], $this->gConfig['tracker_port']);

            return $this->tracker;
        }

        /**
         * 通过tracker获取一个stroage
         * 
         * @param string $groupName 文件组名，当为空时，组名由tracker决定
         * @param FastDFSTrackerClient $tracker
         * @return \FastDFSStorageClient
         */
        public function tracker_query_storage_store($groupName, FastDFSTrackerClient $tracker) {
            $this->storage = new FastDFSStorageClient($this, $groupName, $tracker);

            return $this->storage;
        }

        /**
         * 测试一下tracker服务器是否正常
         * 
         * @param FastDFSTrackerClient $tracker
         * @return boolean
         */
        public function active_test(FastDFSTrackerClient $tracker = null) {
            $this->initTrackerAndStorage($tracker);
            
            $header = self::packHeader(FDFS_PROTO_CMD_ACTIVE_TEST, 0);
            $tracker->send($header);

            $resHeader = self::parseHeader($tracker->read(FDFS_HEADER_LENGTH));

            return $resHeader['status'] == 0 ? true : false;
        }

        public function get_last_error_no() {
            return $this->error['code'];
        }

        public function add_error($errorNo, $info) {
            $this->error['code'] = $errorNo;
            $this->error['msg'] = $info;
        }

        public function get_last_error_info() {
            return $this->error['msg'];
        }

        /**
         * 在storage中删除一个文件
         * 
         * @param string $groupName 文件所在的组名
         * @param string $remoteFile 要删除的文件路径
         * @param FastDFSStorageClient $tracker
         * @param FastDFSStorageClient $storage
         */
        public function storage_delete_file($groupName, $remoteFile, FastDFSStorageClient $tracker, FastDFSStorageClient $storage) {
            $this->initTrackerAndStorage($tracker, $storage, $groupName);

            $this->storage->deleteFile($groupName, $remoteFile);
        }

        /**
         * 往storage中上传一个文件
         * 
         * @param string $localFile 你本地的文件路径
         * @param string $extName 文件的扩展名，当名优提供扩展名时，会自动取文件的扩展名
         * @param array $metas 文件的附加信息
         * @param string $groupName 所在的组名，可以为空，为空时，由tracker决定
         * @param FastDFSTrackerClient $tracker
         * @param FastDFSStorageClient $storage
         */
        public function storage_upload_by_filename($localFile, $extName = '', $metas = array(), $groupName = '', FastDFSTrackerClient $tracker = null, FastDFSStorageClient $storage = null) {
            $this->initTrackerAndStorage($tracker, $storage, $groupName);

            return $this->storage->uploadByFilename($localFile, $extName, $metas);
        }

        /**
         * 上传一个文件的附属文件，主要使用一个图片有缩略图的情况下
         * 
         * @param string $localFile 本地文件的路径，缩略图的文件路径
         * @param string $groupName 组名，最好和主文件在同一个组
         * @param string $masterFileName 主文件名
         * @param string $prefix 文件的前缀
         * @param string $extName 文件的后缀，可以为空，为空时，由tracker决定
         * @param array $meta 附件信息
         * @param FastDFSTrackerClient $tracker
         * @param FastDFSStorageClient $storage
         */
        public function storage_upload_slave_by_filename($localFile, $groupName, $masterFileName, $prefix = '', $extName = '', $meta = array(), FastDFSTrackerClient $tracker = null, FastDFSStorageClient $storage = null) {
            $this->initTrackerAndStorage($tracker, $storage, $groupName);
            /*echo $localFile."<br/>".$groupName."<br/>".$masterFileName."<br/>".$prefix;
            exit;*/
            return $this->storage->uploadSalveFile($localFile, $groupName, $masterFileName, $prefix, $extName, $meta);
        }

        /**
         * 检查这个文件是否已经存在
         * 
         * @param string $groupName 文件所在组名
         * @param string $remoteFile 文件在storage中的名字
         * @param FastDFSStorageClient $tracker
         * @param FastDFSStorageClient $storage
         */
        public function storage_file_exist($groupName, $remoteFile, FastDFSTrackerClient $tracker, FastDFSStorageClient $storage) {
            $this->initTrackerAndStorage($tracker, $storage, $groupName);

            return $this->storage->fileExists($groupName, $remoteFile);
        }

        public function close() {
            if ($this->tracker) {
                $this->tracker->close();
                $this->tracker = null;
            }
        }

        public function tracker_close_all_connections() {
            $this->close();
            if (!$this->storage) {
                $this->storage->close();
            }
        }

        public static function padding($str, $len) {

            $str_len = strlen($str);

            return $str_len > $len ? substr($str, 0, $len) : $str . pack('x' . ($len - $str_len));
        }

        /**
         * 
         * @param int $command
         * @param int $length
         * @return bytes
         */
        public static function packHeader($command, $length = 0) {
            return self::packU64($length) . pack('Cx', $command);
        }

        public static function packMetaData($data) {
            $S1 = "\x01";
            $S2 = "\x02";

            $list = array();
            foreach ($data as $key => $val) {
                $list[] = $key . $S2 . $val;
            };

            return implode($S1, $list);
        }

        public static function parseMetaData($data) {

            $S1 = "\x01";
            $S2 = "\x02";

            $arr = explode($S1, $data);
            $result = array();

            foreach ($arr as $val) {
                list($k, $v) = explode($S2, $val);
                $result[$k] = $v;
            }

            return $result;
        }

        public static function parseHeader($str, $len = FDFS_HEADER_LENGTH) {

            assert(strlen($str) === $len);

            $result = unpack('C10', $str);

            $length = self::unpackU64(substr($str, 0, 8));
            $command = $result[9];
            $status = $result[10];

            return array(
                'length' => $length,
                'command' => $command,
                'status' => $status
            );
        }

        /**
         * From: sphinxapi.php
         */
        private static function unpackU64($v) {
            list ( $hi, $lo ) = array_values(unpack("N*N*", $v));

            if (PHP_INT_SIZE >= 8) {
                if ($hi < 0)
                    $hi += (1 << 32); // because php 5.2.2 to 5.2.5 is totally fucked up again
                if ($lo < 0)
                    $lo += (1 << 32);

                // x64, int
                if ($hi <= 2147483647)
                    return ($hi << 32) + $lo;

                // x64, bcmath
                if (function_exists("bcmul"))
                    return bcadd($lo, bcmul($hi, "4294967296"));

                // x64, no-bcmath
                $C = 100000;
                $h = ((int) ($hi / $C) << 32) + (int) ($lo / $C);
                $l = (($hi % $C) << 32) + ($lo % $C);
                if ($l > $C) {
                    $h += (int) ($l / $C);
                    $l = $l % $C;
                }

                if ($h == 0)
                    return $l;
                return sprintf("%d%05d", $h, $l);
            }

            // x32, int
            if ($hi == 0) {
                if ($lo > 0)
                    return $lo;
                return sprintf("%u", $lo);
            }

            $hi = sprintf("%u", $hi);
            $lo = sprintf("%u", $lo);

            // x32, bcmath
            if (function_exists("bcmul"))
                return bcadd($lo, bcmul($hi, "4294967296"));

            // x32, no-bcmath
            $hi = (float) $hi;
            $lo = (float) $lo;

            $q = floor($hi / 10000000.0);
            $r = $hi - $q * 10000000.0;
            $m = $lo + $r * 4967296.0;
            $mq = floor($m / 10000000.0);
            $l = $m - $mq * 10000000.0;
            $h = $q * 4294967296.0 + $r * 429.0 + $mq;

            $h = sprintf("%.0f", $h);
            $l = sprintf("%07.0f", $l);
            if ($h == "0")
                return sprintf("%.0f", (float) $l);
            return $h . $l;
        }

        private function initTrackerAndStorage(FastDFSTrackerClient $tracker = null, FastDFSStorageClient $storage = null, $groupName = '') {
            $reNewStorage = false;
            if ($tracker && $tracker !== $this->tracker) {
                $this->tracker_get_connection();
            }
            if (($storage && $storage !== $this->storage) || $reNewStorage) {
                $this->tracker_query_storage_store($groupName, $this->tracker);
            }
        }

        /**
         * From: sphinxapi.php
         */
        public static function packU64($v) {


            assert(is_numeric($v));

            // x64
            if (PHP_INT_SIZE >= 8) {
                assert($v >= 0);

                // x64, int
                if (is_int($v))
                    return pack("NN", $v >> 32, $v & 0xFFFFFFFF);

                // x64, bcmath
                if (function_exists("bcmul")) {
                    $h = bcdiv($v, 4294967296, 0);
                    $l = bcmod($v, 4294967296);
                    return pack("NN", $h, $l);
                }

                // x64, no-bcmath
                $p = max(0, strlen($v) - 13);
                $lo = (int) substr($v, $p);
                $hi = (int) substr($v, 0, $p);

                $m = $lo + $hi * 1316134912;
                $l = $m % 4294967296;
                $h = $hi * 2328 + (int) ($m / 4294967296);

                return pack("NN", $h, $l);
            }

            // x32, int
            if (is_int($v))
                return pack("NN", 0, $v);

            // x32, bcmath
            if (function_exists("bcmul")) {
                $h = bcdiv($v, "4294967296", 0);
                $l = bcmod($v, "4294967296");
                return pack("NN", (float) $h, (float) $l); // conversion to float is intentional; int would lose 31st bit
            }

            // x32, no-bcmath
            $p = max(0, strlen($v) - 13);
            $lo = (float) substr($v, $p);
            $hi = (float) substr($v, 0, $p);

            $m = $lo + $hi * 1316134912.0;
            $q = floor($m / 4294967296.0);
            $l = $m - ($q * 4294967296.0);
            $h = $hi * 2328.0 + $q;

            return pack("NN", $h, $l);
        }

    }

    abstract class FastDFSBase {

        abstract public function getSocket();

        abstract public function close();

        public function read($length, $socket = null) {
            if (!$socket) {
                $socket = $this->getSocket();
            }

            if (feof($socket)) {
                throw new FastDFS_Exception('connection unexpectedly closed (timed out?)', $this->_errno);
            }

            $data = stream_get_contents($socket, $length);

            assert($length === strlen($data));

            return $data;
        }

        public function send($data, $length = 0, $socket = null) {
            if (!$socket) {
                $socket = $this->getSocket();
            }

            if (!$length) {
                $length = strlen($data);
            }

            if (feof($socket) || fwrite($socket, $data, $length) !== $length) {
                throw new Exception('connection unexpectedly closed (timed out?)');
            }

            return true;
        }

    }

    class FastDFSTrackerClient extends FastDFSBase {

        private $host;
        private $port;

        /**
         *
         * @var FastDFS
         */
        private $dfs;
        private $_socket;

        public function __construct(FastDFS &$dfs, $host, $port) {
            $this->host = $host;
            $this->port = $port;
            $this->dfs = $dfs;

            $this->_socket = @fsockopen("tcp://$host", $port, $errno, $errstr, $this->dfs->gConfig['connect_timeout']);
            if (!$this->_socket) {
                $this->dfs->add_error(-2, $errstr);
            }
        }

        public function getSocket() {
            return $this->_socket;
        }

        public function close() {
            fclose($this->_socket);
        }

    }

    class FastDFSStorageClient extends FastDFSBase {

        private $groupName;

        /**
         *
         * @var FastDFSTrackerClient 
         */
        private $tracker;

        /**
         *
         * @var FastDFS
         */
        private $dfs;
        private $_socket;
        private $host;
        private $port;
        private $storeIndex;

        public function __construct(FastDFS &$dfs, $groupName, FastDFSTrackerClient $tracker) {
            $this->tracker = $tracker;
            $this->dfs = $dfs;

            $reqBody = '';
            if ($groupName) {
                $cmd = FDFS_QUERY_STORE_WITH_GROUP_ONE;
                $len = FDFS_GROUP_NAME_MAX_LEN;
                $reqBody = FastDFS::padding($groupName, $len);
            } else {
                $cmd = FDFS_QUERY_STORE_WITHOUT_GROUP_ONE;
                $len = 0;
            }
            $reqHeader = FastDFS::packHeader($cmd, $len);
            $this->tracker->send($reqHeader . $reqBody);

            $resHeader = $this->tracker->read(FDFS_HEADER_LENGTH);
            $resInfo = FastDFS::parseHeader($resHeader);

            if ($resInfo['status'] != 0) {
                throw new Exception("something wrong with get storage by group name", $resInfo['status']);
            }

            $resBody = !!$resInfo['length'] ? $this->tracker->read($resInfo['length']) : '';
            $this->groupName = trim(substr($resBody, 0, FDFS_GROUP_NAME_MAX_LEN));
            $this->host = trim(substr($resBody, FDFS_GROUP_NAME_MAX_LEN, FDFS_IP_ADDRESS_SIZE + 1));

            list(,, $this->port) = unpack('N2', substr($resBody, FDFS_GROUP_NAME_MAX_LEN + FDFS_IP_ADDRESS_SIZE - 1, FDFS_PROTO_PKG_LEN_SIZE));

            $this->storeIndex = ord(substr($resBody, -1));

            $this->_socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->dfs->gConfig['connect_timeout']);

            if (!$this->_socket) {
                $this->dfs->add_error($errno, $errstr);
            }
        }

        public function getSocket() {
            return $this->_socket;
        }

        public function getStorePathIndex() {
            return $this->storeIndex;
        }

        public function uploadByFilename($localFile, $extName, $metas) {
            if (!file_exists($localFile)) {
                throw new FastDFSException("$localFile file is not exists");
            }
            $pathInfo = pathinfo($localFile);

            $extName = $extName ? $extName : $pathInfo['extension'];
            $extLen = strlen($extName);

            if ($extLen > FDFS_FILE_EXT_NAME_MAX_LEN) {
                throw new FastDFSException("file ext too long");
            }
            $fp = fopen($localFile, 'rb');
            flock($fp, LOCK_SH);
            $fileSize = filesize($localFile);

            $reqBodyLen = 1 + FDFS_PROTO_PKG_LEN_SIZE + FDFS_FILE_EXT_NAME_MAX_LEN + $fileSize;
            $reqHeader = FastDFS::packHeader(11, $reqBodyLen);
            $reqBody = pack('C', $this->getStorePathIndex()) . FastDFS::packU64($fileSize) . FastDFS::padding($extName, FDFS_FILE_EXT_NAME_MAX_LEN);

            $this->send($reqHeader . $reqBody);

            stream_copy_to_stream($fp, $this->_socket, $fileSize);
            flock($fp, LOCK_UN);
            fclose($fp);

            $resHeader = $this->read(FDFS_HEADER_LENGTH);
            $resInfo = FastDFS::parseHeader($resHeader);

            if ($resInfo['status'] !== 0) {
                return false;
            }
            $resBody = $resInfo['length'] ? $this->read($resInfo['length']) : '';
            $groupName = trim(substr($resBody, 0, FDFS_GROUP_NAME_MAX_LEN));

            $filePath = trim(substr($resBody, FDFS_GROUP_NAME_MAX_LEN));

            if ($metas) {
                $this->setFileMetaData($groupName, $filePath, $metas);
            }

            return array(
                'group_name' => $groupName,
                'filename' => $filePath
            );
        }

        /**
         * 
         * @param type $fileName
         * @param type $groupName
         * @param type $masterfile
         * @param type $prefix
         * @param type $extName
         * @param type $metas
         * @return boolean
         * @throws FastDFSException
         */
        public function uploadSalveFile($fileName, $groupName, $masterfile, $prefix = '', $extName = '', $metas = array()) {
            if (!file_exists($fileName)) {
                throw new FastDFSException("salve file $fileName is not exists");
            }

            $pathInfo = pathinfo($fileName);

            $extName = $extName ? $extName : $pathInfo['extension'];
            $extLen = strlen($extName);

            if ($extLen > FDFS_FILE_EXT_NAME_MAX_LEN) {
                throw new FastDFSException("salve file ext too long");
            }
            $fp = fopen($fileName, 'rb');
            flock($fp, LOCK_SH);

            $fileSize = filesize($fileName);
            $masterFilePathLen = strlen($masterfile);

            $reqBodyLength = 16 + FDFS_FILE_PREFIX_MAX_LEN + FDFS_FILE_EXT_NAME_MAX_LEN + $masterFilePathLen + $fileSize;
            $reqHeader = FastDFS::packHeader(FDFS_PROTO_CMD_UPLOAD_SLAVE_FILE, $reqBodyLength);

            $reqBody = pack('x4N', $masterFilePathLen) . FastDFS::packU64($fileSize) . FastDFS::padding($prefix, FDFS_FILE_PREFIX_MAX_LEN);
            $reqBody .= FastDFS::padding($extName, FDFS_FILE_EXT_NAME_MAX_LEN) . $masterfile;

            $this->send($reqHeader . $reqBody);

            stream_copy_to_stream($fp, $this->_socket, $fileSize);
            flock($fp, LOCK_UN);
            fclose($fp);

            $resHeader = $this->read(FDFS_HEADER_LENGTH);
            $resInfo = FastDFS::parseHeader($resHeader);

            if ($resInfo['status'] !== 0) {
                return false;
            }
            $resBody = $resInfo['length'] ? $this->read($resInfo['length']) : '';
            $groupName = trim(substr($resBody, 0, FDFS_GROUP_NAME_MAX_LEN));

            $filePath = trim(substr($resBody, FDFS_GROUP_NAME_MAX_LEN));

            if ($metas) {
                $this->setFileMetaData($groupName, $filePath, $metas);
            }

            return array(
                'group_name' => $groupName,
                'filename' => $filePath
            );
        }

        public function deleteFile($groupName, $fileName) {
            $reqBodyLen = strlen($fileName) + FDFS_GROUP_NAME_MAX_LEN;
            $reqHeader = FastDFS::packHeader(FDFS_PROTO_CMD_DELETE_FILE, $reqBodyLen);
            $reqBody = FastDFS::padding($groupName, FDFS_GROUP_NAME_MAX_LEN) . $fileName;

            $this->send($reqHeader . $reqBody);

            $resHeader = $this->read(FDFS_HEADER_LENGTH);
            $resInfo = FastDFS::parseHeader($resHeader);

            return !$resInfo['status'];
        }

        public function fileExists($groupName, $filePath) {
            $meta = $this->getFileMeta($groupName, $filePath);

            return $meta ? true : false;
        }

        public function setFileMetaData($groupName, $filePath, array $metaData, $flag = FDFS_OVERWRITE_METADATA) {

            $metaData = FastDFS::packMetaData($metaData);
            $metaDataLength = strlen($metaData);
            $filePathLength = strlen($filePath);
            $flag = $flag === FDFS_OVERWRITE_METADATA ? 'O' : 'M';

            $reqBodyLength = (FDFS_PROTO_PKG_LEN_SIZE * 2) + 1 + $metaDataLength + $filePathLength + FDFS_GROUP_NAME_MAX_LEN;

            $reqHeader = FastDFS::packHeader(FDFS_PROTO_CMD_SET_METADATA, $reqBodyLength);

            $reqBody = FastDFS::packU64($filePathLength) . FastDFS::packU64($metaDataLength);
            $reqBody .= $flag . FastDFS::padding($groupName, FDFS_GROUP_NAME_MAX_LEN) . $filePath . $metaData;

            $this->send($reqHeader . $reqBody);

            $resHeader = $this->read(FDFS_HEADER_LENGTH);
            $resInfo = FastDFS::parseHeader($resHeader);

            return !$resInfo['status'];
        }

        /**
         * 取得文件的元信息，如果文件不存在则，返回false，反正是一个关联数组
         * 
         * @param type $groupName
         * @param type $filePath
         * @return boolean
         */
        public function getFileMeta($groupName, $filePath) {
            $reqBodyLength = strlen($filePath) + FDFS_GROUP_NAME_MAX_LEN;
            $reqHeader = FastDFS::packHeader(FDFS_PROTO_CMD_GET_METADATA, $reqBodyLength);
            $reqBody = FastDFS::padding($groupName, FDFS_GROUP_NAME_MAX_LEN) . $filePath;

            $this->send($reqHeader . $reqBody);

            $resHeader = $this->read(FDFS_HEADER_LENGTH);
            $resInfo = FastDFS::parseHeader($resHeader);

            if (!!$resInfo['status']) {
                return false;
            }

            $resBody = $resInfo['length'] ? $this->read($resInfo['length']) : false;

            return FastDFS::parseMetaData($resBody);
        }

        public function close() {
            fclose($this->_socket);
        }

    }

    class FastDFSException extends Exception {
        
    }

}
