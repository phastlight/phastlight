<?php
namespace Phastlight\Module\NET;

class Socket extends \Phastlight\EventEmitter
{
    private $socket; //the underlying uv socket
    private $type; //the socket type
    private $shouldClose; //whether this socket should close or not

    public function setSocket($socket)
    {
        $this->socket = $socket; 
    }

    public function getSocket()
    {
        return $this->socket; 
    }

    public function setType($type)
    {
        $this->type = $type; 
    }

    public function getType()
    {
        return $this->type; 
    }

    public function setShouldClose($shouldClose)
    {
        $this->shouldClose = $shouldClose; 
    }

    public function getShouldClose()
    {
        return $this->shouldClose;
    }

    public function connect($port, $host, $connectionListener) 
    {
        $self = $this;
        $client = uv_tcp_init();
        uv_tcp_connect($client, uv_ip4_addr($host,$port), function($uvSocket, $stat) use ($self, $connectionListener, &$client) {
            $self->setType("tcp4");
            if ($stat == 0) {
                $self->setSocket($uvSocket);
                $self->emit("connect"); 
                if (is_callable($connectionListener)) {
                    $connectionListener(); 
                }

                //start reading data from server...
                uv_read_start($uvSocket, function($uvSocket, $nread, $buffer) use ($socket) {
                    $shouldClose = $socket->getShouldClose();
                    if ($shouldClose) {
                        uv_close($uvSocket); 
                        $self->emit('close');
                    }
                });

            }
        });

        return $this;
    }

    public function write($data, $callback = '')
    {
        if ($this->socket) {
            $self = $this;
            $socket = $this->socket;
            uv_write($socket, $data, function($uvSocket, $stat) use ($self,$callback) {
                if ($stat == 0) {
                    uv_read_start($uvSocket, function($uvSocket, $nread, $buffer) use ($self) {
                        if ($nread > 0) {
                            $self->emit('data', $buffer);
                        }
                        $self->emit('end'); //send the "end" event
                        if (is_callable($callback)) {
                            $callback(); 
                        }

                        //finally, see if we should close the socket or not
                        $shouldClose = $self->getShouldClose();
                        if ($shouldClose) {
                            uv_close($uvSocket); 
                            $self->emit('close');
                        }

                    });
                }

                //finally, see if we should close the socket or not, we need to do it again in case the socket is not closed in read callback
                $shouldClose = $self->getShouldClose();
                if ($shouldClose) {
                    uv_close($uvSocket); 
                    $self->emit('close');
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
