<?php

if (defined("PHP_VERSION_ID")) {
	echo "version is set to " . PHP_VERSION_ID;
} else {
	echo "version is not set";
}

phpinfo();