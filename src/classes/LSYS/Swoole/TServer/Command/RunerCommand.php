<?php
namespace LSYS\Swoole\TServer\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LSYS\Swoole\TServer\Command;
use Symfony\Component\Console\Input\InputArgument;
use LSYS\Swoole\TServer\Exception;
class RunerCommand extends Command
{
    protected function configure()
    {
        $this
        ->setName('runer')
        ->setDescription('create object and run it.')
        ->setHelp("This command allows you to create object and run it.")
        ->addArgument("operation",InputArgument::REQUIRED,"build|list")
        ->addArgument("app",InputArgument::OPTIONAL,"see your config")
        ;
    }
    protected $config=[];
    public function setConfig(array $config)
    {
        foreach ($config as $k=>$v){
            if(!isset($v['class'])
                ||!isset($v['class'][0])
                ||!class_exists($v['class'][0])
                ||!isset($v['method'])
                ||!isset($v['method'][0])
                ||!method_exists($v['class'][0], $v['method'][0])
             )continue;
             $this->config[$k]=$v;
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation=$input->getArgument("operation");
        switch ($operation){
            case"build":
                $app=$input->getArgument("app");
                if (empty($app)) $app=array_keys($this->config);
                else $app=explode(",", $app);
                $this->runexec($app,$output);
                break;
            case"list":
                if (count($this->config)==0) {
                    $msg="run is empty";
                }else{
                    $msg="app:".implode("\n", array_keys($this->config));
                }
                $output->writeln($msg);
                break;
            default:
                throw new Exception("not support :".$operation);
        }
    }
    protected function runexec($app,OutputInterface $output) {
        foreach ($app as $app_) {
            if (!isset($this->config[$app_]))continue;
            $run=function()use($app_,$output){
                $cls=$this->config[$app_]['class'];
                $method=$this->config[$app_]['method'];
                $output->writeln("app:".$app_." create");
                $obj=(new \ReflectionClass($cls[0]))->newInstanceArgs(isset($cls[1])&&is_array($cls[1])?$cls[1]:[]);
                $output->writeln("app:".$app_." start");
                if(!method_exists($obj, $method[0])){
                    $output->writeln("app:".$app_." config method wrong");
                    return ;
                }
                call_user_func_array([$obj,$method[0]],isset($method[1])?[$method[1]]:[]);
                $output->writeln("app:".$app_." success");
            };
            if($this->config[$app_]['go']??0)go($run);
            else $run();
        }
    }
}