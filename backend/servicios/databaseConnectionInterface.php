<?php

interface DatabaseConnectionInterface
{
  public function connect();
  public function query($query);
  public function close();
}
