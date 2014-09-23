<?php
define("DEBUG",true) ;
class Cache
{
    /** 缓存目录 **/
    var $CacheDir = 'F:\workspace\zhuangled';
    /** 缓存的文件 **/
    var $CacheFile = '';
    /** 文件缓存时间(分钟) **/
    var $CacheTime = 0;
    /** 文件是否已缓存 **/
    var $CacheFound = False;
    /** 错误及调试信息 **/
    var $DebugMsg = NULL;
    function Cache($CacheTime = 0)
    {
        $this->CacheTime = $CacheTime;
    }
    private function Run()
    {
        /** 缓存时间大于0,检测缓存文件的修改时间,在缓存时间内为缓存文件名,超过缓存时间为False，
        小于等于0,返回false,并清理已缓存的文件
        **/
        Return $this->CacheTime ? $this->CheckCacheFile() : $this->CleanCacheFile();
    }
    function GetCache($VistUrl, $CacheFileType = 'html')
    {
        $this->SetCacheFile($VistUrl, $CacheFileType);
        $fileName = $this->CheckCacheFile();
        if ($fileName) {
            $fp       = fopen($fileName, "r");
            $content_ = fread($fp, filesize($fileName));
            fclose($fp);
            return $content_;
        } else {
            return false;
        }
    }
    private function SetCacheFile($VistUrl, $CacheFileType = 'html')
    {
        if (empty($VistUrl)) {
            /** 默认为index.html **/
            $this->CacheFile = 'index';
        } else {
            /** 传递参数为$_POST时 **/
            $this->CacheFile = is_array($VistUrl) ? implode('.', $VistUrl) : $VistUrl;
        }
        $this->CacheFile = $this->CacheDir . '/' . md5($this->CacheFile);
        $this->CacheFile .= '.' . $CacheFileType;
    }
    function SetCacheTime($t = 60)
    {
        $this->CacheTime = $t;
    }
    private function CheckCacheFile()
    {
        if (!$this->CacheTime || !file_exists($this->CacheFile)) {
            Return False;
        }
        /** 比较文件的建立/修改日期和当前日期的时间差 **/
        $GetTime = (Time() - Filemtime($this->CacheFile)) / (60 * 1);
        /** Filemtime函数有缓存,注意清理 **/
        Clearstatcache();
        $this->Debug('Time Limit ' . ($GetTime * 60) . '/' . ($this->CacheTime * 60) . '');
        $this->CacheFound = $GetTime <= $this->CacheTime ? $this->CacheFile : False;
        Return $this->CacheFound;
    }
    function SaveToCacheFile($VistUrl, $Content, $CacheFileType = 'html')
    {
        $this->SetCacheFile($VistUrl, $CacheFileType);
        if (!$this->CacheTime) {
            Return False;
        }
        /** 检测缓存目录是否存在 **/
        if (true === $this->CheckCacheDir()) {
            $CacheFile = $this->CacheFile;
            $CacheFile = str_replace('//', '/', $CacheFile);
            $fp        = @fopen($CacheFile, "wb");
            if (!$fp) {
                $this->Debug('Open File ' . $CacheFile . ' Fail');
            } else {
                if (@!fwrite($fp, $Content)) {
                    $this->Debug('Write ' . $CacheFile . ' Fail');
                } else {
                    $this->Debug('Cached File');
                }
                ;
                @fclose($fp);
            }
        } else {
            /** 缓存目录不存在，或不能建立目录 **/
            $this->Debug('Cache Folder ' . $this->CacheDir . ' Not Found');
        }
    }
    private function CheckCacheDir()
    {
        if (file_exists($this->CacheDir)) {
            Return true;
        }
        /** 保存当前工作目录 **/
        $Location = getcwd();
        /** 把路径划分成单个目录 **/
        $Dir      = split("/", $this->CacheDir);
        /** 循环建立目录 **/
        $CatchErr = True;
        for ($i = 0; $i < count($Dir); $i++) {
            if (!file_exists($Dir[$i])) {
                /** 建立目录失败会返回False 返回建立最后一个目录的返回值 **/
                $CatchErr = @mkdir($Dir[$i], 0777);
            }
            @chdir($Dir[$i]);
        }
        /** 建立完成后要切换到原目录 **/
        chdir($Location);
        if (!$CatchErr) {
            $this->Debug('Create Folder ' . $this->CacheDir . ' Fail');
        }
        Return $CatchErr;
    }
    private function CleanCacheFile()
    {
        if (file_exists($this->CacheFile)) {
            @chmod($this->CacheFile, 777);
            @unlink($this->CacheFile);
        }
        /** 置没有缓存文件 **/
        $this->CacheFound = False;
        Return $this->CacheFound;
    }
    function Debug($msg = '')
    {
        if (DEBUG) {
            $this->DebugMsg[] = '[Cache]' . $msg;
        }
    }
    function GetError()
    {
        Return empty($this->DebugMsg) ? '' : "<br>n" . implode("<br>n", $this->DebugMsg);
    }
}
/* end of class */

?>