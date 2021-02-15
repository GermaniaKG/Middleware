<?php
namespace Germania\Middleware;

trait LogLevelTrait
{


    /**
     * @var string
     */
    public $loglevel = \Psr\Log\LogLevel::INFO;


    /**
     * Sets the PSR-3 Success LogLevel name.
     *
     * @param string $loglevel
     */
    public function setLogLevel(string $loglevel) : self
    {
        $this->loglevel = $loglevel;
        return $this;
    }
}
