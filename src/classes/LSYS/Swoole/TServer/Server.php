<?php
namespace LSYS\Swoole\TServer;

use LSYS\Swoole\Thrift\Server\TBinaryProtocolAcceleratedFactory;
use Thrift\ClassLoader\ThriftClassLoader;
use LSYS\Swoole\TServer\Server\HandlerProxy;
use LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Convert;
use LSYS\Swoole\Thrift\Server\SwooleEventManager;
use LSYS\Swoole\Thrift\Server\SwooleSubject;
use LSYS\Swoole\Thrift\Server\SwooleEvent;
use LSYS\EventManager\CallbackObserver;
use LSYS\Swoole\TServer\Server\Swoole\TaskManager;
use LSYS\Swoole\TServer\Server\Swoole\TimerManager;
abstract class Server
{
    /**
     * thrift加载器对象
     * @var object
     */
    private $loader;
    /**
     * swoole对象
     * @var object
     */
    private $swoole;
    /**
     * thrift 服务器对象
     * @var object
     */
    private $server;
    /**
     * 服务构造器
     * @var ServerBuilder
     */
    private $builder;
    /**
     * @var TaskManager
     */
    private $task_manger;
    /**
     * @var TimerManager
     */
    private $timer_manger;
    /**
     * 服务器事件管理器
     * @var SwooleEventManager
     */
    protected $event_manager;
    /**
     * 转换器
     * @var \LSYS\Swoole\TServer\Server\Convert
     */
    protected $convert;
    /**
     * 中间件列表
     * @var Middleware[]
     */
    protected $middleware=[];
    /**
     * 服务基类
     * @param ServerBuilder $builder
     */
    public function __construct(ServerBuilder $builder,SwooleEventManager $event_manager=null) {
        $this->builder=$builder;
        $this->event_manager=$event_manager?$event_manager:new SwooleEventManager();
        $this->event_manager->attach((new SwooleSubject(SwooleEvent::WorkerStart))->attach(
            new CallbackObserver(function(){
                $title=@cli_get_process_title();
                if(!empty($title))return ;
                Kernel::processName($title."-worker");
            })
        ));
        $this->bootstrap();
    }
    /**
     * 得到任务管理器(不太建议用)
     * @return \LSYS\Swoole\TServer\Server\Swoole\TaskManager
     */
    public function taskManager(){
        if(!$this->task_manger)$this->task_manger=new TaskManager($this);
        return $this->task_manger;
    }
    /**
     * 得到定期任务管理器(不太建议用)
     * @return \LSYS\Swoole\TServer\Server\Swoole\TimerManager
     */
    public function timerManager(){
        if(!$this->timer_manger)$this->timer_manger=new TimerManager($this);
        return $this->timer_manger;
    }
    /**
     * 获得服务器事件管理器
     * @return \LSYS\Swoole\Thrift\Server\SwooleEventManager
     */
    public function getEventManager(){
        return $this->event_manager;
    }
    /**
     * 得到构建服务的构建对象
     * @return \LSYS\Swoole\TServer\ServerBuilder
     */
    public function getServerBuilder(){
        return $this->builder;
    }
    /**
     * 设置或得到thrift加载器
     * @param \Thrift\ClassLoader\ThriftClassLoader $loader
     * @return \Thrift\ClassLoader\ThriftClassLoader||$this
     */
    public function thriftLoader(\Thrift\ClassLoader\ThriftClassLoader $loader=null)
    {
        if(!is_null($loader)){
            $this->loader=$loader;
            return $this;
        }
        if (!$this->loader) {
            $this->loader=new ThriftClassLoader();
        }
        return $this->loader;
    }
    /**
     * 模型转换器
     * @return \LSYS\Swoole\TServer\Server\Convert
     */
    public function convert(){
        if(!is_object($this->convert)){
            $this->convert=new Convert(
                [new \LSYS\Swoole\TServer\Server\Convert\ThriftRender\ArrRender()],
                [new \LSYS\Swoole\TServer\Server\Convert\ArrayRender\ArrRender()]
            );
        }
        return $this->convert;
    }
    /**
     * 通讯协议
     * @return \LSYS\Swoole\Thrift\Server\TBinaryProtocolAcceleratedFactory
     */
    public function protocolFactory()
    {
        return new TBinaryProtocolAcceleratedFactory();
    }
    /**
     * 得到使用的中间件列表
     * @return \LSYS\Swoole\TServer\Server\Middleware[]
     */
    public function middleware() {
        return $this->middleware;
    }
    /**
     * 按指定配置构建一个swoole对象[运行时系统调用]
     * @param \Swoole\Server $swoole
     * @return \LSYS\Swoole\TServer\Server
     */
    public function makeServer($swoole)
    {
        $this->loader&&$this->loader->register();
        $handler = new HandlerProxy($this,$this->handler());
        foreach ($this->middleware as $v) {
            $handler->addMiddleware($v);
        }
        $processor = $this->processor($handler);
        $protocol = $this->protocolFactory();
        $this->server = new \LSYS\Swoole\Thrift\Server\TSwooleServer($processor, $swoole, $protocol, $protocol);
        return $this;
    }
    /**
     * 按指定配置构建一个swoole对象[运行时系统调用]
     * @param array $config
     * @return \LSYS\Swoole\TServer\Server
     */
    public function makeSwoole(array $config)
    {
        $config=$config+array(
            "sock_type"=>SWOOLE_SOCK_TCP,
            "host"=>"0.0.0.0",
            "port"=>"8099",
            "setting"=>array()
        );
		if(\Swoole\Coroutine::getuid()!=-1){
            throw new Exception("can't create swoole server in coroutine");
        }
        $swoole=new \Swoole\Server($config['host'], $config['port'], $config['sock_type']);
        $this->swoole=$swoole;
        return $this;
    }
    /**
     * 运行中的thrift对象
     * @return \LSYS\Swoole\Thrift\Server\TSwooleServer
     */
    public function server(){
        return $this->server;
    }
    /**
     * 运行中的swoole对象
     * @return \Swoole\Server
     */
    public function swoole(){
        return $this->swoole;
    }
    /**
     * 执行服务
     * @return boolean
     */
    public function run()
    {
        $status = $this->server()->serve();
        return $this->terminate($status);
    }
    /**
     * 服务关闭时回调函数
     * @param int $status 服务关闭状态码
     * @return int
     */
    protected function terminate($status){
        return $status;
    }
    /**
     * 服务启动时回调函数
     */
    abstract protected function bootstrap();
    /**
     * 服务处理器
     * 自行实现服务接口
     * @return object
     */
    abstract public function handler();
    /**
     * 服务进程
     * 由thrift生成
     * @param object $handler
     * @return object
     */
    abstract public function processor($handler);
}