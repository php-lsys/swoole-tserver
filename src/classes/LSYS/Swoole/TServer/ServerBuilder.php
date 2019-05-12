<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer;
use LSYS\Swoole\TServer\Command\ServerCommand;
class ServerBuilder
{   
    protected $server_create;
    protected $server_command;
    protected $config_name;
    protected $config=[];
    /**
     * 服务构建器
     * @param callable|string $server_create 服务类或服务构建回调函数,返回Server对象
     * @param string $config_name app名,也是配置键值名
     */
    public function __construct($server_create,$config_name) {
        $this->config_name=$config_name;
        $this->server_create=$server_create;
    }
    /**
     * 对的内部服务对象名(APP名)
     * @return mixed
     */
    public function configName() {
        return str_replace(".", "_", $this->config_name);
    }
    /**
     * 设置内部对象配置
     * @param array $config
     * @return \LSYS\Swoole\TServer\ServerBuilder
     */
    public function config(array $config){
        $this->config=$config;
        return $this;
    }
    /**
     * 得到进行创建服务对象的命令对象
     * 未build时为NULL
     * @return \LSYS\Swoole\TServer\Command\ServerCommand
     */
    public function buildServerCommand(){
        return $this->server_command;
    }
    /**
     * 编译一个内部服务对象
     * @param ServerCommand $command
     * @throws Exception
     * @return \LSYS\Swoole\TServer\Server
     */
    public function build(ServerCommand $command) {
        $this->server_command=$command;
        /**
         * @var Server $server
         */
        if (is_callable($this->server_create)) {
            $server=call_user_func($this->server_create,$this);
        }else if (is_string($this->server_create)&&class_exists($this->server_create)) {
            $server=(new \ReflectionClass($this->server_create))->newInstance($this);
        }
        if (!isset($server)||!$server instanceof Server) {
            throw new Exception(strtr("server class [:class] not extends :dclass",[":class"=>is_scalar($this->server_create)?$this->server_create:"[*]",":dclass"=>Server::class]));
        }
        $config=$this->config;
        $swoole_server=$server->makeSwoole($config)
            ->makeServer($server->swoole())
            ->server();
        $config['setting']['daemonize']=0;//一定不能后台.
        $swoole_server->config($config['setting']);
        return $server;
    }
}