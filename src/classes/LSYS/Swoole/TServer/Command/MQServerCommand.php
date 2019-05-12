<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use LSYS\Swoole\TServer\CommandDeamon;
use LSYS\Swoole\TServer\Kernel;
use LSYS\MQS\DataDeCode;
use LSYS\Swoole\TServer\MQServer;
use LSYS\Swoole\TServer\Command;
use LSYS\Swoole\TServer\MQServerProcess;
use LSYS\Swoole\TServer\Exception;
class MQServerCommand extends Command
{
    use CommandDeamon;
    protected $mq=[];
    protected $process=[];
    protected $attach_process=[];
    /**
     * 设置配置
     * 过滤错误配置
     * @param array $config
     * @return boolean|\LSYS\Swoole\TServer\Command\MQServerCommand
     */
    public function setConfig(array $config){
        foreach ($config as $name=>$config_){
            if(!isset($config_['server'])
                ||!isset($config_['topic'])
                ||!is_array($config_['topic'])
                ||!is_array($config_['server'])
                ||!isset($config_['server'][0])
                )continue;
            $config_['topic']=array_filter($config_['topic']);
            if(empty($config_['topic']))continue;
            $decode=$config_['decode']??NULL;
            if(!empty($decode[0])){
                $_decode=new \ReflectionClass($decode[0]);
                if(!$_decode->implementsInterface(DataDeCode::class))continue;
            }
            $sr=new \ReflectionClass($config_['server'][0]);
            if(!$sr->isSubclassOf(MQServer::class))continue;
            if($sr->implementsInterface(MQServerProcess::class)){
                $process=intval($config_['attach_process']??1);
                $this->attach_process[$name]=array(
                    $config_['server'][0],
                    $process<=0?1:$process
                );
            }
            $process=intval($config_['process']??1);
            $this->process[$name]=$process<=0?1:$process;
            $this->mq[$name]=array(
                $config_['server'],$config_['topic'],$decode
          );
        }
        return $this;
    }
    protected function configure()
    {
        $this
        ->setName('mq')
        ->addArgument("operation",InputArgument::REQUIRED,"start|stop|kill|topic")
        ->addArgument("app",InputArgument::OPTIONAL,"")
        ->setDescription('server tool.')
        ->setHelp("This command is start message queue server.");
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation=$input->getArgument("operation");
        switch ($operation){
            case"start":
                $topic=$input->getArgument("app");
                if (empty($topic)) $topic=array_keys($this->mq);
                else $topic=explode(",", $topic);
                $this->createStart($topic,$output);
            break;
            case"stop":
            case"kill":
                $this->stopDeamon("-mq", $output,$operation=='kill');
            break;
            case"topic":
                if (count($this->_server_builder)==0) {
                    $output->writeln("app is empty");
                }else{
                    $output->writeln("app:");
                    foreach (array_keys($this->_server_builder) as $name) {
                        $output->writeln(" ".$name);
                    }
                }
            break;
            default:
                throw new Exception("not support :".$operation);
        }
    }
    protected function createStart($names_,OutputInterface $output){
        $this->startInit();
        Kernel::processName($this->getApplication()->getName()."-mq");
        $names=array_filter($names_);
        foreach ($names as $k=>$name){
            if(!isset($this->mq[$name])){
                unset($names[$k]);
                continue;
            }
            $pnum=$this->process[$name];
            while($pnum-->0){
                $process=call_user_func_array([$this,'createMQ'],array_merge([$name],$this->mq[$name]));
                if(!$process)break;
                $this->signalAddProcess($name.".".$pnum, $process);
            }
            if(isset($this->attach_process[$name])){
                $pnum=$this->attach_process[$name][1];
                $topic=array_keys($this->mq[$name][1]);
                while($pnum-->0&&count($topic)>0){
                    $process=$this->createMQAttach($name,$this->attach_process[$name][0],$topic);
                    if(!$process)break;
                    $this->signalAddProcess($name."..".$pnum, $process);
                }
            }
        }
        if(count($names))$this->signalListen(function($name_)use($output){
            $name_=explode(".",$name_);
            list($name)=$name_;
            if(count($name_)==2){
                return call_user_func_array([$this,'createMQ'],array_merge([$name],$this->mq[$name]));
            }else{
                return $this->createMQAttach($name,$this->attach_process[$name][0],array_keys($this->mq[$name][1]));
            }
        });
        else{
            $output->writeln("not mq is run, your args :".implode(",", $names_));
            $this->shutdownClean();
        }
    }
    protected function createMQ($app,array $server,array $topics,$decode=null){
        $process = new \Swoole\Process(function()use($server,$decode,$topics,$app){
            Kernel::processName($this->getApplication()->getName()."-mq-".$app);
            $server=(new \ReflectionClass($server[0]))->newInstanceArgs(isset($server[1])&&is_array($server[1])?$server[1]:[]);
            assert($server instanceof MQServer);
            if($decode){
                $decode=(new \ReflectionClass($decode[0]))->newInstanceArgs(isset($decode[1])&&is_array($decode[1])?$decode[1]:[]);
            }
            foreach ($topics as $topic=>$class){
                $server->addTopic($topic,$class,$decode);
            }
            exit($server->listen());
        }, false, false);
        if(!$process->start()){
            return false;
        }
        return $process;
    }
    protected function createMQAttach($app,$callback,array $topic){
        $process = new \Swoole\Process(function()use($callback,$topic,$app){
            Kernel::processName($this->getApplication()->getName()."-mq-attr-".$app);
            exit(call_user_func([$callback,"attachProcess"],$topic));
        }, false, false);
        if(!$process->start()){
            return false;
        }
        return $process;
    }
}