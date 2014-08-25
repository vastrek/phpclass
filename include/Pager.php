<?php
/**
 * 分页类
 * @author frank
 * @date 2013-04-26
 * @email fengxuting@gmail.com
 */
class Pager{
	private $url ;
	/**
	 * 参数名
	 */
	private $param_name ;
	/**
	 * 当前页
	 */
	private $currPage ;
	/**
	 * 页面大小
	 */
	private $pageSize ;
	/**
	 * 总行数
	 */
	private $totalCount ;
	/**
	 * 总页数
	 */
	private $totalPage ;
    /**
     * 首页
     *
     * @var string
     */
    public $first_page;

    /**
     * 上一页
     *
     * @var string
     */
    public $pre_page;

    /**
     * 下一页
     *
     * @var string
     */
    public $next_page;

    /**
     * 最后一页
     *
     * @var string
     */
    public $last_page;
	
	function Pager(){
		$this->currPage = !empty($_GET['page'])?$_GET['page']:1 ;
		$this->pageSize = 10 ;
		
        $this->first_page         = '首页';
        $this->pre_page           = '上一页';
        $this->next_page          = '下一页';
        $this->last_page          = '末页';
        
		$this->param_name = "page" ;
	}
	/**
	 * 获取总页数
	 */
	public function getTotalPage(){
		if(!$this->totalCount){
			return false ;
		}
		return ceil($this->totalCount/$this->pageSize) ;
	}
	public function setTotalCount($totalCount){
		$this->totalCount = $totalCount ;
	}
	public function getTotalCount(){
		return $this->totalCount ;
	}
	/**
	 * 页面大小
	 */
	public function pageSize($pageSize = null){
		if(is_null($pageSize)){
			$pageSize = 10 ;
		}
		$this->pageSize = $pageSize ;
		return $this ;
	}
	/**
	 * 前一页
	 */
	public function get_prev_page(){
		$string = "";
		if($this->currPage<=1){
			$string .= "<a href=\"javascript:;\">上一页</a>" ;
		}else{
			$string .= "<a href=\"".$this->url()."page=".($this->currPage-1)."\">上一页</a>" ;
		}
		return $string ;
	}
	/**
	 * 首页 前一页
	 */
	public function get_first_page(){
		$string = "<ul class=\"ulfy\">" ;
		$string.= "<li><a href=\"".$this->url().$this->param_name."=1\">&lt;&lt;</a></li>" ;
		if($this->currPage<=1){
			$string .= "<li><a href=\"javascript:;\">&lt;</a></li>" ;
		}else{
			$string .= "<li><a href=\"".$this->url()."page=".($this->currPage-1)."\">&lt;</a></li>" ;
		}
		return $string ;
	}
	/**
	 * 后一页 末页
	 */
	public function get_last_page(){
		
		$string = "" ;
		if($this->currPage>=$this->getTotalPage()){
			$string .= "<li><a href=\"javascript:;\">&gt;</a></li>" ;
		}else{
			$string .= "<li><a href=\"".$this->url().$this->param_name."=".($this->currPage+1)."\">&gt;</a></li>" ;
		}
		$string .= "<li><a href=\"".$this->url().$this->param_name."=".$this->getTotalPage()."\">&gt;&gt;</a></li>" ;
		$string .= "</ul>" ;
		return $string ;
	}
	/**
	 * 后一页
	 */
	public function get_next_page(){
		$string = "" ;
		if($this->currPage>=$this->getTotalPage()){
			$string .= "<a href=\"javascript:;\">后一页</a>" ;
		}else{
			$string .= "<a href=\"".$this->url().$this->param_name."=".($this->currPage+1)."\">后一页</a>" ;
		}
		return $string ;
	}
	
	/**
	 * 列表页
	 */
	public function get_list(){
		$string = "" ;
		if($this->getTotalPage()<=10){
			for($i=1;$i<=$this->getTotalPage();$i++){
				if($i==$this->currPage){
					$string .= "<a href=\"javascript:;\" class=\"on\">".$i."</a>" ;
				}else{
					$string .= "<a href=\"".$this->url().$this->param_name."=".$i."\">".$i."</a>" ;
				}
			}
		}else{
			for($i=1;$i<=3;$i++){
				if($i==$this->currPage){
					$string .= "<a href=\"javascript:;\" class=\"on\">".$i."</a>" ;
				}else{
					$string .= "<a href=\"".$this->url().$this->param_name."=".$i."\">".$i."</a>" ;
				}
			}
			
			
			if($this->currPage>5)$string .= "..." ;
			if($this->currPage>4){
				$min = $this->currPage-1 ;
			}else{
				$min = 4 ;
			}
			if($this->currPage<$this->getTotalPage()-4){
				$max = $this->currPage+2 ;
			}else{
				$max = $this->getTotalPage()-2 ;
			}
			//if($max >= $min+2){
				for($i=$min;$i<=$max-1;$i++){
					if($i==$this->currPage){
						$string .= "<a href=\"javascript:;\" class=\"on\">".$i."</a>" ;
					}else{
						$string .= "<a href=\"".$this->url().$this->param_name."=".$i."\">".$i."</a>" ;
					}
				}						
			//}
				
			if($this->currPage<$this->getTotalPage()-4)$string .= "..." ;
			
			
			
			for($i=$this->getTotalPage()-2;$i<=$this->getTotalPage();$i++){
				if($i==$this->currPage){
					$string .= "<a href=\"javascript:;\" class=\"on\">".$i."</a>" ;
				}else{
					$string .= "<a href=\"".$this->url().$this->param_name."=".$i."\">".$i."</a>" ;
				}
			}			
		}
		
		return $string ;
	}
	/**
	 * 输出分页
	 */
	public function output(){
		//return $this->get_first_page().$this->get_list().$this->get_last_page() ;
		//return "<ul>".$this->get_prev_page().$this->get_list().$this->get_next_page()."</ul>" ;
		return $this->get_prev_page().$this->get_list().$this->get_next_page() ;
	}
	/**
	 * 页面链接
	 */
	public function url(){
		$request_uri = $_SERVER["REQUEST_URI"] ;
		$request_uri = str_replace("&page=".$this->currPage,"",str_replace("?page=".$this->currPage,"",$request_uri)) ;
		if(strpos($request_uri,"?")>-1){
			$request_uri = $request_uri."&" ;
		}else{
			$request_uri = $request_uri."?" ;
		}
		return $request_uri ; 		
	}
}
?>
