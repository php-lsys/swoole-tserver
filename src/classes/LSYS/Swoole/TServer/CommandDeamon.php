<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * 后台进程辅助片段 
 */
trait CommandDeamon
{
    /**
     * 是否后台执行
     * @var integer
     */
    protected $deamon=0;
    /**
     * 进程ID文件
     * @var string
     */
    protected $pid_file;
    /**
     * @var \Swoole\Process
     */
    protected $child_pids=[];
    /**
     * 设置进程是否为后台运行
     * @param bool $daemon
     * @return static
     */
    public function setDaemon($daemon) {
        $this->deamon=$daemon;
        return $this;
    }
    /**
     * 设置进程PID文件
     * @param string $pid_file
     * @return static
     */
    public function setPidFile($pid_file) {
        $this->pid_file=$pid_file;
        return $this;
    }
    /**
     * 服务启动初始化函数
     * 完成必要环境检测和设置
     * @throws Exception
     */
    private function startInit(){
        if(empty($this->pid_file)){
            throw new Exception("plase set pid file path.");
        }
        if(is_file($this->pid_file)){
            throw new Exception("pid file is exist,plase delete it.");
        }
        if (!is_writable(dirname($this->pid_file))){
            throw new Exception("pid file dir can't writable.");
        }
        if($this->deamon)\Swoole\Process::daemon(true, true);
        if(!@file_put_contents($this->pid_file,getmypid())){
            throw new Exception("pid file writable fail.");
        }
    }
    /**
     * 设置指定名称的处理进程
     * 用于当关闭时清理和自动重启
     * @param string $name
     * @param \Swoole\Process $process
     */
    private function signalAddProcess($name,\Swoole\Process $process){
        $this->child_pids[$name]=$process;
    }
    /**
     * 移除指定名称的处理进程
     * @param string $name
     * @return \LSYS\Swoole\TServer\CommandDeamon
     */
    private function signalDelProcess($name){
        unset($this->child_pids[$name]);
        return $this;
    }
    /**
     * 关闭时清理
     */
    private function shutdownClean(){
        if (is_file($this->pid_file))@unlink($this->pid_file);
    }
    /**
     * 信号处理,实现进程移除时重启
     * @param callable $process_create($name) 返回新建立指定名称进程对象
     */
    private function signalListen(callable $process_create){
        \Swoole\Process::signal(SIGCHLD, function($sig)use($process_create){
            while($ret =  \Swoole\Process::wait(false)) {
                //array('code' => 0, 'pid' => 15001, 'signal' => 15);
                $name=null;
                foreach ($this->child_pids as $n=>$p){
                    if($p->pid==$ret['pid']){
                        $name=$n;break;
                    }
                }
                if($name){
                    $this->signalDelProcess($name);
                    try{
                        $process=call_user_func($process_create,$name);
                    }catch (\Exception $e){
                        continue;
                    }
                    if ($process instanceof \Swoole\Process) {
                        $this->signalAddProcess($name, $process);
                    }
                }
            }
            if(count($this->child_pids)==0)exit(0);
        });
        $exit=function(){
            if(count($this->child_pids)==0)$isExit=true;
            foreach ($this->child_pids as $k=>$p){
                \Swoole\Process::kill($p->pid,SIGTERM);
                unset($this->child_pids[$k]);
            }
            $this->child_pids=[];
            $this->shutdownClean();
            if(isset($isExit))exit(0);
        };
        \Swoole\Process::signal(SIGTERM,$exit);
        \Swoole\Process::signal(SIGINT,$exit);
    }
    /**
     * 停止进程辅助方法
     * @param string $filter 过滤进程后缀
     * @param OutputInterface $output
     * @param boolean $is_kill
     */
    private function stopDeamon($filter,OutputInterface $output,$is_kill=false){
        if (is_file($this->pid_file)&&$pid=file_get_contents($this->pid_file)) {
            @\Swoole\Process::kill($pid,SIGTERM);
        }
        if(!$is_kill)return ;
        if(is_file($this->pid_file))unlink($this->pid_file);
        $pname=Kernel::processName($this->getApplication()->getName().$filter,true);
        $cmd="ps -fe |grep '{$pname}' | grep -v 'grep' |awk '{print $2}'";
        $ret=null;
        @exec($cmd,$ret);
        if(is_array($ret))foreach ($ret as $pid){
            \Swoole\Process::kill($pid,SIGKILL);
            $output->writeln("kill:".$pid);
        }
    }
}
