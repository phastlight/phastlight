<?php
namespace Phastlight\Module\NET;

class Socket extends \Phastlight\EventEmitter
{
    private $connection;
    private $type; //the socket type
    private $shouldClose; //whether this socket should close or not

    public function setType($type)
    {
        $this->type = $type; 
    }

    public function getType()
    {
        return $this->type; 
    }

    public function connect($port, $host, $connectionListener) 
    {
        $self = $this;
        $this->connection = uv_tcp_init();
        $client = $this->connection;
        uv_tcp_connect($this->connection, uv_ip4_addr($host,$port), function($stream, $stat) use ($self, $connectionListener, &$client) {
            $self->setType("tcp4");
            if ($stat == 0) {
                $self->emit("connect"); 
                if (is_callable($connectionListener)) {
                    $connectionListener(); 
                }

                //start reading data from server...
                uv_read_start($client, function($stream, $nread, $buffer) use ($self) {
                    if ($nread > 0) { //we got some data from server
                        $self->emit('data', $buffer);
                    }
                    
                    if ($self->shouldClose) {
                        uv_close($stream); 
                        $self->emit('close');
                    }
                });

            }
        });

        return $this;
    }

    public function write($data, $callback = '')
    {
        if ($this->connection) {
            $self = $this;
            uv_write($this->connection, $data, function($stream, $stat) use ($callback) {
                if ($stat == 0) {
                    //data is written out, execute the callback
                    if (is_callable($callback)) {
                        $callback(); 
                    }
                }
            });
        }
    }

    public function end($data = null)
    {
        if ($data) {
            $this->write($data);
            $this->end();
        }
        else{
            $this->shouldClose = true; 
        }
    }

}
