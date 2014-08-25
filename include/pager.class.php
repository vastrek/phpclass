<?php
/**
 * pager class file
 *
 * 分页类
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: pager.class.php 1.3 2011-11-13 21:10:01Z tommy $
 * @package libraries
 * @since 1.0
 */
class pager{

		public $param_name;
    /**
     * 连接网址
     *
     * @var string
     */
    public $url;

    /**
     * 当前页
     *
     * @var integer
     */
    public $page;

    /**
     * list总数
     *
     * @var integer
     */
    public $total;

    /**
     * 分页总数
     *
     * @var integer
     */
    public $total_pages;

    /**
     * 每个页面显示的post数目
     *
     * @var integer
     */
    public $num;

    /**
     * list允许放页码数量,如:1.2.3.4就这4个数字,则$per_circle为4
     *
     * @var integer
     */
    public $per_circle;

    /**
     * 分页程序的扩展功能开关,默认关闭
     *
     * @var boolean
     */
    public $ext;

    /**
     * list中的坐标. 如:7,8,九,10,11这里的九为当前页,在list中排第三位,则$center为3
     *
     * @var integer
     */
    public $center;

    /**
     * 第一页
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

    /**
     * 分页附属说明
     *
     * @var string
     */
    public $note;

    /**
     * 是否为ajax分页模式
     *
     * @var boolean
     */
    public $isAjax;

    /**
     * ajax分页的动作名称
     *
     * @var string
     */
    public $ajax_action_name;

    /**
     * 分页隐藏开关
     *
     * @var boolean
     */
    public $hidden_status;


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->ext                = false;
        $this->center             = 3;
        $this->num                = 20;
        $this->per_circle         = 10;
        $this->isAjax             = false;
        $this->hidden_status      = false;

        //define pager style params
        $this->first_page         = '第一页';
        $this->pre_page           = '上一页';
        $this->next_page          = '下一页';
        $this->last_page          = '最末页';
        
        $this->param_name="page";

        return true;
    }

    /**
     * 获取总页数
     *
     * @return integer
     */
    private function get_total_page() {

        if (!$this->total) {
            return false;
        }

        return ceil($this->total / $this->num);
    }

    /**
     * 获取当前页数
     *
     * @return integer
     */
    private function get_page_num() {

        $page = (!$this->page) ? 1 : (int)$this->page;

        //当URL中?page=5的page参数大于总页数时
        return ($page > $this->total_pages) ? (int)$this->total_pages : $page;
    }

    /**
     * 返回$this->num=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function num($num = null) {

        //参数分析
        if (is_null($num)) {
            $num = 10;
        }

        $this->num = (int)$num;

        return $this;
    }

    /**
     * 返回$this->total=$total_post.
     *
     * @param integer $total_post
     * @return $this
     */
    public function total($total_post = null) {

        $this->total = (!is_null($total_post)) ? (int)$total_post : 0;

        return $this;
    }

    /**
     * 开启分页的隐藏功能
     *
     * @access public
     * @param boolean $item    隐藏开关 , 默认为true.
     * @return $this
     */
    public function hide($item = true) {

        if ($item === true) {
            $this->hidden_status = true;
        }

        return $this;
    }

    /**
     * 返回$this->url=$url.
     *
     * @param string $url
     * @return $this
     */
    public function url($url = null) {

        //当url为空时,自动获取url参数. 注:默认当前页的参数为page
        if (is_null($url)) {

            //当网址没有参数时
            $url = (!$_SERVER['QUERY_STRING']) ? $_SERVER['REQUEST_URI'] . ((substr($_SERVER['REQUEST_URI'], -1) == '?') ? ($this->param_name.'=' ): ('?'.$this->param_name.'=')) : '';

            //当网址有参数时,且有分页参数(page)时
            if (!$url && (stristr($_SERVER['QUERY_STRING'], $this->param_name.'='))) {
                $url = str_ireplace($this->param_name.'=' . $this->page, '', $_SERVER['REQUEST_URI']);

                $end_str = substr($url, -1);
                if ($end_str == '?' || $end_str == '&') {
                    $url .= $this->param_name.'=';
                } else {
                    $url .= '&'.$this->param_name.'=';
                }
            }
            //当网址中未发现含有分页参数(page)时
            if (!$url) {
                $url = $_SERVER['REQUEST_URI'] . '&'.$this->param_name.'=';
            }
        }

        //自动获取都没获取到url...额..没有办法啦, 趁早返回false
        if (!$url) {
           return false;
        }
        $this->url = trim($url);

        return $this;
    }

    /**
     * 返回$this->page=$page.
     *
     * @param integer $page
     * @return $this
     */
    public function page($page = null) {

        //当参数为空时.自动获取GET['page']
        if (is_null($page)) {
        	  if(isset($_GET[$this->param_name]))
            $page = (int)$_GET[$this->param_name];
            $page = (!$page) ? 1 : $page;
        }

        if(!$page) {
           return false;
        }

        $this->page = $page;

        return $this;
    }
    
     /**
     * 返回$this->param_name=$param_name.
     *
     * @param string $param_name
     * @return $this
     */
    public function param_name($param_name = null) {
    	  if(!is_null($param_name)){
        	$this->param_name = $param_name;
      	}
        return $this;
    }

    /**
     * 返回$this->ext=$ext.
     *
     * @param boolean $ext
     * @return $this
     */
    public function ext($ext = true) {

        //将$ext转化为小写字母.
        $this->ext = ($ext) ? true : false;

        return $this;
    }

    /**
     * 返回$this->center=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function center($num) {

        if (!$num) {
            return false;
        }

        $this->center = (int)$num;

        return $this;
    }

    /**
     * 返回$this->per_circle=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function circle($num) {

        if (!$num) {
            return false;
        }

        $this->per_circle = (int)$num;

        return $this;
    }

    /**
     * 处理第一页,上一页
     *
     * @return string
     */
    private function get_first_page() {

        if ($this->page == 1 || $this->total_pages <= 1) {
            return false;
        }


            $string = '<a href="' . $this->url . '1" target="_self">' . $this->first_page . '</a><a href="' . $this->url . ($this->page - 1). '" target="_self">' . $this->pre_page . '</a>';

        return $string;
    }

    /**
     * 处理下一页,最后一页
     *
     * @return string
     */
    private function get_last_page() {

        if ($this->page == $this->total_pages || $this->total_pages <= 1) {
            return false;
        }

            $string = '<a href="' . $this->url . ($this->page + 1) . '" target="_self">' . $this->next_page . '</a><a href="' . $this->url . $this->total_pages . '" target="_self">' . $this->last_page . '</a>';

        return $string;
    }

    /**
     * 处理注释内容
     *
     * @return string
     */
    private function get_note() {

        if (!$this->ext || !$this->note) {
            return false;
        }

        return str_replace(array('{$total_num}', '{$total_page}', '{$num}'), array($this->total, $this->total_pages, $this->num), $this->note);
    }

    /**
     * 处理list内容
     *
     * @return string
     */
    private function get_list() {

        if (empty($this->total_pages) || empty($this->page)) {
            return false;
        }

        if ($this->total_pages > $this->per_circle) {
            if ($this->page + $this->per_circle >= $this->total_pages + $this->center) {
                $list_start   = $this->total_pages - $this->per_circle + 1;
                $list_end     = $this->total_pages;
            } else {
                $list_start   = ($this->page>$this->center) ? $this->page - $this->center + 1 : 1;
                $list_end     = ($this->page>$this->center) ? $this->page + $this->per_circle-$this->center : $this->per_circle;
            }
        } else {
            $list_start       = 1;
            $list_end         = $this->total_pages;
        }

        $pagelist_queue = '';
        for ($i=$list_start; $i<=$list_end; $i++) {
            $pagelist_queue  .= ($this->page == $i) ? '<a href="javascript:void(0);" class="curpage">'.$i.'</a>'  :  '<a href="' . $this->url . $i . '" target="_self">' . $i . '</a>';
        }

        return $pagelist_queue;
    }

    /**
     * 开启ajax分页模式
     *
     * @param string $action    动作名称
     * @return $this
     */
    public function ajax($action) {

        if ($action) {
            $this->isAjax             = true;
            $this->ajax_action_name   = $action;
        }

        return  $this;
    }

    /**
     * 输出处理完毕的HTML
     *
     * @return string
     */
    public function output() {

        //支持长的url.
        $this->url         = trim(str_replace(array("\n","\r"), '', $this->url));

        //获取总页数.
        $this->total_pages = $this->get_total_page();

        //获取当前页.
        $this->page        = $this->get_page_num();

        return  $this->get_note() . $this->get_first_page() . $this->get_list() . $this->get_last_page() ;
    }

    /**
     * 输出下拉菜单式分页的HTML(仅限下拉菜单)
     *
     * @return string
     */
    public function select() {

        //支持长的url.
        $this->url         = trim(str_replace(array("\n","\r"), '', $this->url));

        //获取总页数.
        $this->total_pages = $this->get_total_page();

        //获取当前页.
        $this->page        = $this->get_page_num();

        $string = '<select name="doitphp_select_pagelist" class="pagelist_select_box" onchange="self.location.href=this.options[this.selectedIndex].value">';
        for ($i = 1; $i <= $this->total_pages; $i ++) {
            $string .= ($i == $this->page) ? '<option value="' . $this->url . $i . '" selected="selected">' . $i . '</option>' : '<option value="' . $this->url . $i . '">' . $i . '</option>';
        }
        $string .= '</select>';

        return $string;
    }

  
}