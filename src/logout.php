<?php
session_start();
session_destroy();
header("Location: /animal-foods/");
exit;
