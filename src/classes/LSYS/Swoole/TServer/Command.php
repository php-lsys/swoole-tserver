<?php
namespace LSYS\Swoole\TServer;
class Command extends \Symfony\Component\Console\Command\Command
{
    protected $kernel;
    /**
     * 命令基类
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel=$kernel;
        parent::__construct();
    }
    public function getKernel() {
        return $this->kernel;
    }
}