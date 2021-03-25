<?php

use exert\DirectClient;

require './vendor/autoload.php';

$dc = new DirectClient('dddd');

for ($i = 0; $i <= 100; ++$i) {
    $dc->publish("is publish $i");
}
