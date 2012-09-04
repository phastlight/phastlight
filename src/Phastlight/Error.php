<?php
namespace Phastlight;

class Error extends Object
{
  private $severity;
  private $message;
  private $file_path;
  private $line;

  public function __construct($severity, $message, $file_path, $line)
  {
    $this->severity = $severity;
    $this->message = $message;
    $this->file_path = $file_path;
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
    return $this->file_path;
  }

  public function getLine()
  {
    return $this->line;
  }
}
