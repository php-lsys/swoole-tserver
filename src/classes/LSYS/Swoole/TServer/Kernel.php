<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer;
use LSYS\Swoole\TServer\Command\ServerCommand;
use Symfony\Component\Console\Application;
use LSYS\Swoole\TServer\Command\MQServerCommand;
use LSYS\Swoole\TServer\Command\RunerCommand;

class Kernel
{
    /**
     * 进程名获取或设置辅助函数
     * @param string $processTitle
     * @param boolean $return 是否只返回不设置
     * @return string|boolean
     */
    public static function processName($processTitle,$return=false,$php="") {
        $processTitle=preg_replace("/[^0-9a-z]/i", "-", strtolower($processTitle));
        $processTitle=preg_replace("/\-+/", "-", strtolower($processTitle));
        $processTitle=$php.trim($processTitle,"-");
        if($return)return $processTitle;
        if (\function_exists('cli_set_process_title')) {
            if (!@cli_set_process_title($processTitle)) {
                if ('Darwin' === PHP_OS) {
                    return false;
                } else {
                    return cli_set_process_title($processTitle);
                }
            }
        } elseif (\function_exists('setproctitle')) {
            setproctitle($processTitle);
            return true;
        }
        return false;
    }
    protected $config_;
    protected $console_;
    /**
     * 得到内部使用命令行APP对象
     * @return \Symfony\Component\Console\Application
     */
    public function getConsole()
    {
        if (is_null($this->console_)) {
            return $this->console_ = new Application($this->name,$this->version);
        }
        return $this->console_;
    }
    protected function setConsole(Application $app)
    {
        $this->console_=$app;
        return $this;
    }
    protected $name;
    protected $version;
    protected $server_command;
    protected $run_command;
    protected $mq_command;
    /**
     * 服务启动内核
     * 启动类
     * @param string $config
     */
    public function __construct($config="swoole.server"){
        $this->config_=\LSYS\Config\DI::get()->config($config);
        $this->name=$this->config_->get("name","swoole");
        $this->version=$this->config_->get("version","0.0.1");
        $this->addRunerServer();        
        $this->addAppServer();
        $this->addMqServer();
    }
    /**
     * 得到使用的配置对象
     * @return \LSYS\Config
     */
    public function getConfig() {
        return $this->config_;
    }
    protected function addRunerServer(){
        $this->run_command=new RunerCommand($this);
        $this->run_command->setConfig($this->config_->get("runer",[]));
    }
    protected function addMqServer(){
        $this->mq_command=new MQServerCommand($this);
        $deamon=$this->config_->get("mq.daemon",0);
        $this->mq_command->setDaemon($deamon);
        $pid_file=$this->config_->get("mq.pid_file","mq.pid");
        $this->mq_command->setPidFile($pid_file);
        $this->mq_command->setConfig($this->config_->get("mq.apps",[]));
    }
    protected function addAppServer(){
        $this->server_command=new ServerCommand($this);
        $deamon=$this->config_->get("app.daemon",0);
        $this->server_command->setDaemon($deamon);
        $pid_file=$this->config_->get("app.pid_file","thrift.pid");
        $this->server_command->setPidFile($pid_file);
    }
    /**
     * 添加服务构建者
     * @param ServerBuilder $server_builder
     * @return \LSYS\Swoole\TServer\Kernel
     */
    public function addServerBuilder(ServerBuilder $server_builder){
        $config=$this->config_->get("app.apps.".$server_builder->configName(),[]);
        $server_builder->config($config);
        $this->server_command->addServer($server_builder);
        return $this;
    }
    /**
     * 进行服务处理
     * @param object $input
     * @param object $output
     * @return number
     */
    public function handle($input=null,$output=null) {
        $console=$this->getConsole();
        $console->add($this->server_command);
        $console->add($this->mq_command);
        $console->add($this->run_command);
        return $console->run($input,$output);
    }
}
