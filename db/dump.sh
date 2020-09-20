#!/bin/bash

. /base.sh

mysqldump -h$HOST -u$USER -p$PASSWORD --no-data --databases ${DB} > structure.sql
mysqldump -h$HOST -u$USER -p$PASSWORD --no-create-info --no-create-db --databases ${DB} > data.sql
