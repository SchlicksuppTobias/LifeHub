<?php
$passwort = 'test';
$hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$passwordHash = password_hash($passwort, $hashAlgo);

var_dump($passwordHash);