<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
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