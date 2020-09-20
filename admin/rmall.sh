#!/usr/bin/env bash
cd /tmp && find . -type f -not -name "faileddomains" -not -name "max_level_category.json" -mmin +59 -exec rm {} \;
#rm -fr /tmp/writecategoryjson*
#rm -fr /tmp/writeitemsjson*
#rm -fr /tmp/update*
#rm -fr /tmp/getlist*
#rm -fr /tmp/getfeed*
#rm -fr /tmp/yelp*
#rm -fr /tmp/read*
##rm -fr /tmp/*\.json
#rm -fr /tmp/*.log
> /tmp/xdebug.log
#service apache2 restart
