<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Convert;
trait TraitArrayConvert{
    protected $mode;
    protected $fix_;
    protected $_fix;
    protected $fill_;
    protected $_fill;
    /**
     * 数组结构转换辅助片段
     * 1 键名驼峰转换为下划线 2 键名转换为大写 3 键名转换为小写 4 键名下划线转换为驼峰
     * @param int $mode 本类常量
     * @param array $_fix 前缀替换
     * @param array $fix_ 后缀替换
     * @param array $_fill 前缀填充
     * @param array $fill_ 后缀填充
     */
    public function __construct($mode=null,$_fix=null,$fix_=null,$_fill=null,$fill_=null) {
        $this->mode=$mode;
        $this->fix_=$fix_;
        $this->_fix=$_fix;
        $this->fill_=$fill_;
        $this->_fill=$_fill;
    }
    /**
     * 下划线转驼峰
     */
    private function convertUnderline($words, $isUcFirst = true, $separator = '_')
    {
        $words = $separator . str_replace($separator, " ", strtolower($words));
        
        $str = ltrim(str_replace(" ", "", ucwords($words)), $separator);
        
        return $isUcFirst ? ucfirst($str) : $str;
    }
    /**
     * 驼峰命名转下划线命名
     */
    private function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);
            return trim($str, "_");
    }
    /**
     * 根据当前属性进行数组格式转换
     * @param array $data
     * @param array $map
     * @return array
     */
    private function arrayChange(array $data,array $map){
        $arr=$data;
        if(is_array($map)&&count($map)>0){
            foreach ($map as $key=>$value) {
                $arr[$value]=isset($data[$key])?$data[$key]:null;
            }
        }
        $mode=$this->mode;
        $tmp=[];
        switch ($mode){
            case 2:
                foreach ($arr as $key=>$value) {
                    $tmp[strtoupper($key)]=$value;
                }
                break;
            case 1:
                foreach ($arr as $key=>$value) {
                    $tmp[$this->humpToLine($key)]=$value;
                }
                break;
            case 4:
                foreach ($arr as $key=>$value) {
                    $tmp[$this->convertUnderline($key,false)]=$value;
                }
                break;
            case 3:
                foreach ($arr as $key=>$value) {
                    $tmp[strtolower($key)]=$value;
                }
                break;
            default:$tmp=$arr;
        }
        $fix=$this->_fix??null;
        $arr=[];
        $in=false;
        if (!empty($fix)) {
            $in=true;
            $len=strlen($fix);
            foreach ($tmp as $key=>$value){
                if(strpos($key, $fix)==0)$key=substr($key, $len);
                $arr[$key]=$value;
            }
        }
        $fix=$this->fix_??null;
        if (!empty($fix)) {
            if($in)$tmp=$arr;
            $in=true;
            $len=strlen($fix);
            $fix=strrev($fix);
            foreach ($tmp as $key=>$value){
                $key=strrev($key);
                if(strpos($key, $fix)==0)$key=strrev(substr($key, $len));
                $arr[$key]=$value;
            }
        }
        $fill=$this->_fill??null;
        if (!empty($fill)) {
            if($in)$tmp=$arr;
            $in=true;
            foreach ($tmp as $key=>$value){
                $arr[$fill.$key]=$value;
            }
        }
        $fill=$this->fill_??null;
        if (!empty($fill)) {
            if($in)$tmp=$arr;
            $in=true;
            foreach ($tmp as $key=>$value){
                $arr[$key.$fill]=$value;
            }
        }
        if(!$in)$arr=$tmp;
        return $arr;
    }
}