<?php
$c = new mysqli('127.0.0.1', 'root', '', '', 3306);
$r = $c->query('SHOW DATABASES');
while($row = $r->fetch_assoc()) {
    print_r($row);
}
