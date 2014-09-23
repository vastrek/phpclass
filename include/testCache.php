<?php
include_once('Cache.php') ;
$cache = new Cache(1) ;

if($cache->GetCache('count','txt')){
	echo $cache->GetCache('count','txt') ;	
}else{
    echo "no cache" ;
    $cache->SaveToCacheFile("count", "100", $CacheFileType = 'txt') ;
}	
?>