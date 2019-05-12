<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LSYS\Swoole\TServer\ServerBuilder;
use Symfony\Component\Console\Input\InputArgument;
use LSYS\Swoole\TServer\CommandDeamon;
use LSYS\Swoole\TServer\Kernel;
use LSYS\Swoole\TServer\Command;
use LSYS\Swoole\TServer\Exception;
class ServerCommand extends Command
{
    use CommandDeamon;
    /**
     * @var ServerBuilder[]
     */
    protected $_server_builder=[];
    /**
     * 添加服务构建者
     * @param ServerBuilder $server_builder
     * @return static
     */
    public function addServer(ServerBuilder $server_builder){
        $this->_server_builder[$server_builder->configName()]=$server_builder;
        return $this;
    }
    protected function configure()
    {
        $this
        ->setName('server')
        ->addArgument("operation",InputArgument::REQUIRED,"start|stop|kill|list")
        ->addArgument("app",InputArgument::OPTIONAL,"")
        ->setDescription('server tool.')
        ->setHelp("This command is start thrift server.");
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation=$input->getArgument("operation");
        switch ($operation){
            case"start":
                $app=$input->getArgument("app");
                if (empty($app)) $app=array_keys($this->_server_builder);
                else $app=explode(",", $app);
                $this->createStart($app,$output);
            break;
            case"stop":
            case"kill":
                $this->stopDeamon("-app", $output,$operation=='kill');
            break;
            case"list":
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
    protected function createStart($names,OutputInterface $output){
        $this->startInit();
        Kernel::processName($this->getApplication()->getName()."-app");
        $apps=$this->createServers($names,$output);
        foreach ($apps as $name=>$process){
            $this->signalAddProcess($name, $process);
        }
        if(count($apps)){
            $this->signalListen(function($name)use($output,$apps){
                $ps=$this->createServers([$name], $output);
                return isset($ps[$name])?$ps[$name]:false;
            });
        }else{
            $output->writeln("not app is run, your args :".implode(",", $names));
            $this->shutdownClean();
        }
    }
    protected function createServers(array $names,OutputInterface $output) {
        $names=array_filter($names);
        $builders=array_intersect_key($this->_server_builder, array_flip($names));
        $process_list=[];
        foreach ($builders as $name=>$builder) {
            assert($builder instanceof ServerBuilder);
            $process = new \Swoole\Process(function()use($builder,$name){
                $server=$builder->build($this);
                Kernel::processName($this->getApplication()->getName()."-app-".$name);
                exit($server->run());
            }, false, false);
            if(!$process->start()){
                $output->writeln("create server fail:[{$name}]");
                continue;
            }
            $process_list[$name]=$process;
        }
        return $process_list;
    }
}