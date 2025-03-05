<?php

class CurlExtensionNotLoadedException extends Exception
{
    public function __construct()
    {
        parent::__construct('The PHP curl extension is not loaded. Please correct this before proceeding!');
    }
}