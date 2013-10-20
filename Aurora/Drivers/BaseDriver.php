<?php

namespace Aurora\Drivers;

abstract class BaseDriver
{
    abstract public function getConnection();
}