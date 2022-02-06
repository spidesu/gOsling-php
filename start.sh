#!/bin/bash

envsubst < "conf/config.env.json" > "conf/config.json"
php init.php