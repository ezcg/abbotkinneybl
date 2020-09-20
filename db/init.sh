#!/bin/bash

. base.sh

mysql -u${USER} -p${PASSWORD} < structure.sql
#mysql -u${USER} -p${PASSWORD} < data.sql
