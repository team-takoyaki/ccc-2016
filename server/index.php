<?php

require_once 'lib/limonade.php';
dispatch('/', 'hello');
    function hello()
    {
        return 'Hello world!';
    }
run();
