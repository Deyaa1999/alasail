<?php
$c = new mysqli('127.0.0.1', 'root', '', 'gadgetzone', 3306);
$r = $c->query('SELECT * FROM settings');
while($row = $r->fetch_assoc()) {
    print_r($row);
}
