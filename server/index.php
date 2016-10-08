<?php

require_once 'lib/limonade.php';

dispatch('/', 'index');
function index() {
  return html('index.html');
}

dispatch('/sell', 'sell');
function sell() {
  return html('sell.html');
}

run();
