#!/bin/bash

envsubst < "/app/conf/config.env.json" > "/app/conf/config.json"
php init.php