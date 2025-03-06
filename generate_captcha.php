<?php
session_start();
$_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
echo $_SESSION['captcha_code'];
