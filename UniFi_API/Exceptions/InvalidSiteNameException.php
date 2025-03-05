<?php

class InvalidSiteNameException extends Exception
{
    public function __construct()
    {
        parent::__construct('Invalid site name provided.');
    }
}