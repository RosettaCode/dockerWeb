<?php

echo "SHA-512: " . hash("sha512", $HTTP_ENV_VARS['HTTP_TO_HASH']) . "\n";
echo "\n";
echo json_encode($HTTP_ENV_VARS, JSON_PRETTY_PRINT) . "\n";
