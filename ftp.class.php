<?php
class Ftp
{

    private $hostname = '';
    private $username = '';
    private $password = '';
    private $port = '21';
    private $timeout = 10;
    private $debug = true;
    private $conn_id = false;

    public function __construct() {
        
    }

    /**
     * FTP 连接
     * @param array $config $config = array('hostname'=>'','username'=>'','password'=>'','port'=>''...);
     */
    public function connect($config = arr) {
        if (count($config) < 1)
            return false;

        $this->_init($config);
        if (false === $this->conn_id = ftp_connect($this->hostname, $this->port, $this->timeout)) {
            if ($this->debug === true) {
                $this->_log('ftp unable to connect');
            }
            return false;
        }

        if (!$this->_login()) {
            if ($this->debug === true) {
                $this->_log('ftp unable to login');
            }
            return false;
        }
        $this->_log('ftp connect and login in');
        return true;
    }


    /**
     * FTP 下载
     * @param string $source
     * @param string $target
     * @param type $mode  FTP_BINARY,FTP_ASCII
     * @return boolean
     */ 
    public function download($source, $target, $mode = FTP_BINARY){
        if (!$this->_isconn()) {
            return false;
        }
        $result = @ftp_get($this->conn_id, $target, $source, $mode);

        if ($result === false) {
            if ($this->debug === true) {
                $this->_log('ftp_unable_to_download: '.$source.'to '.$target.' ');
            }
            return false;
        }
        
        $this->_log('ftp down success');

        return true;
    }
    
    /**
     * 检查FTP中有这个文件
     * @param string $filename
     * @return boolean
     */
    public function existsFile($filename = ''){
        $file_list = $this->fileList();
        if (!is_array($file_list)) {
            return false;
        }
  
        if(in_array($filename, $file_list)){
            return true;
        } else{
            return false;
        }
        
        
    }
    
    /**
     * 获取文件修改时间
     * @param string $filename
     * @return boolean
     */
    public function getFileTime($filename =''){
        if (!$this->_isconn()) {
            return false;
        }
        if( -1 == $time = ftp_mdtm($this->conn_id, $filename) ){
            return false;
        }else{
            return $time;
        }
    }

    public function getFileDetail($filename, $path = '.') {
        if (!$this->_isconn()) {
            return false;
        }
        $fileList = $this->getFileListDetail($path);

        if (!is_array($fileList))
            return false;
        $find_file_info = '';
        foreach ($fileList as $file_info) {

            if (strpos($file_info, $filename)) {
                $find_file_info = $file_info;
                break;
            }
        }

        if ($find_file_info === '')
            return false;

        return $this->parseFileInfo($find_file_info);
    }

    public function parseFileInfo($file_info) {
        $chunks = preg_split("/\s+/", $file_info);
        list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks;
        $item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file';
        return $item;
    }

    public function getFileListDetail($path = '.') {
        if (!$this->_isconn()) {
            return false;
        }

        return ftp_rawlist($this->conn_id, $path, true);
    }

    /**
     * 获取目录文件列表
     * @return array
     */
    public function fileList($path = '.') {
        if (!$this->_isconn()) {
            return false;
        }
        return ftp_nlist($this->conn_id, $path);
    }

    /**
     * 关闭FTP
     *
     */
    public function close() {
        if (!$this->_isconn()) {
            return FALSE;
        }

        $this->_log('ftp close');
        return @ftp_close($this->conn_id);
    }

    /**
     * FTP变量初始化
     * @param array $config 配置数组
     */
    private function _init($config = array()) {
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $this->$key = $val;
            }
        }
        //特殊字符过滤
        $this->hostname = preg_replace('|.+?://|', '', $this->hostname);
    }

    /**
     * FTP 登陆
     */
    private function _login() {
        return @ftp_login($this->conn_id, $this->username, $this->password);
    }

    /**
     * 检查是否为资源句柄
     */
    private function _isconn() {
        if (!is_resource($this->conn_id)) {
            if ($this->debug === true) {
                $this->_log('ftp_no_connection');
            }
            return false;
        }

        return true;
    }
    
    /**
     * 日志
     * @param type $info
     */
    private function _log($info) {
        access_log('FTP', $info);
    }

}