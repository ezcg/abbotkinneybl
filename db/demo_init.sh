#!/bin/bash

# Populate db with data for demonstration purposes. DB structure is in repo, data is not.
# This grabs data from s3 if need be.
cd /db/
. ./base.sh

mysql -u${USER} -p${PASSWORD} < structure.sql

if test -f "data.sql"; then
  mysql -u${USER} -p${PASSWORD} < data.sql
elif test -f "data.sql.zip"; then
  unzip data.sql.zip
  mysql -u${USER} -p${PASSWORD} < data.sql
else
  wget https://s3.us-east-2.amazonaws.com/ezcg.com/data.sql.zip
  unzip data.sql.zip
  mysql -u${USER} -p${PASSWORD} < data.sql
fi

