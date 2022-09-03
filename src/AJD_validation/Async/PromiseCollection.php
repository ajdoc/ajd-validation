<?php 

namespace AJD_validation\Async;

class PromiseCollection
{
	private $started = false;
    private $collection = [];

 	public function __invoke()
    {
		if($this->started) 
		{
		    return;
		}

		$this->started = true;
		$this->start();
    }

    public function collect($promise)
    {
        if (
        	!\is_object($promise) || 
        	!\method_exists($promise, 'then') || 
        	!\method_exists($promise, 'cancel')
        ) 
        {
            return;
        }

        $length = \array_push($this->collection, $promise);

        if($this->started && 1 === $length) 
        {
            $this->start();
        }
    }

    private function start()
    {
        for ($i = key($this->collection); isset($this->collection[$i]); $i++) 
        {
            $promise = $this->collection[$i];

            $exception = null;

            try 
            {
                $promise->cancel();
            } 
            catch (\Throwable $exception) 
            {

            } 
            catch (\Exception $exception) 
            {

            }

            unset($this->collection[$i]);

            if($exception) 
            {
                throw $exception;
            }
        }

        $this->collection = [];
    }
}