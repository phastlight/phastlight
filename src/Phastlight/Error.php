<?php
namespace Phastlight;

class Error extends Object
{
    private $severity;
    private $message;
    private $filePath;
    private $line;

    public function __construct($severity, $message, $filePath, $line)
    {
        $this->severity = $severity;
        $this->message = $message;
        $this->filePath = $filePath;
        $this->line = $line;
    }

    public function getSeverity()
    {
        return $this->severity;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getLine()
    {
        return $this->line;
    }
}
