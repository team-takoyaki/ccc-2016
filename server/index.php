<?php

require_once 'lib/limonade.php';

dispatch('/', 'index');
function index() {
  return html('index.html');
}

run();
