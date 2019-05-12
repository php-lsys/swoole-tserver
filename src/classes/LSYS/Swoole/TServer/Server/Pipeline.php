<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TException;

class Pipeline
{
    /**
     * The object being passed through the pipeline.
     *
     * @var mixed
     */
    protected $passable;

    /**
     * The array of class pipes.
     *
     * @var Middleware[]
     */
    protected $pipes = [];

    /**
     * Set the object being sent through the pipeline.
     *
     * @param  mixed  $passable
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param  array|mixed  $pipes
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param  \Closure  $destination
     * @return Response
     */
    public function then(\Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }
    /**
     * Get the final piece of the Closure onion.
     *
     * @param  \Closure  $destination
     * @return \Closure
     */
    protected function prepareDestination(\Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (\Exception $e) {
                return $this->handleException($passable, $e);
            } catch (\Throwable $e) {
                return $this->handleException($passable, new FatalThrowableError($e));
            }
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        return $pipe($passable, $stack);
                    } else {
                        $parameters = [$passable, $stack];
                    }
                    return $pipe->handle(...$parameters);
                } catch (\Exception $e) {
                    return $this->handleException($passable, $e);
                } catch (\Throwable $e) {
                    return $this->handleException($passable, new FatalThrowableError($e));
                }
            };
        };
    }
    /**
     * Handle the given exception.
     *
     * @param  mixed  $passable
     * @param  \Exception  $e
     * @return mixed
     *
     * @throws \Exception
     */
    protected function handleException($passable, \Exception $e)
    {
        if(!$e instanceof TException||!method_exists($e, "write")){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR,$e);
            $message=$e->getMessage().":".$e->getCode();
            if(\LSYS\Core::$environment!=\LSYS\Core::PRODUCT&&method_exists($e, "getTraceAsString")){
                $message.="\n".$e->getTraceAsString();//非线上环境 把堆栈输出,方便调试
            }
            $e = new TApplicationException($message, TApplicationException::UNKNOWN);
        }
        return new Response($e);
    }
}
