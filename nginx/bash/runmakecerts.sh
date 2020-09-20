#! /bin/bash

if [ $# -eq 0 ]; then
  read -p "Enter domain name to make local cert for: " domain
  mkcert ${domain}
else
  domain=$1
  mkcert ${domain}
fi

printf "\nMade cert for $domain\n"

echo """
==================================================
  Copying certificates
==================================================
"""

if [ -f /.dockerenv ]; then
  # inside docker
  mv ${domain}\.pem /etc/nginx/certs/${domain}.crt
  mv ${domain}-key\.pem /etc/nginx/certs/${domain}.key
  cd /etc/nginx/certs
  chmod 600 *
  cd /etc/nginx/certs
  pwd
  ls -ltr
else
  # on host machine
  mv ${domain}\.pem ../certs/${domain}.crt
  mv ${domain}-key\.pem ../certs/${domain}.key
  cd ../certs
  chmod 600 *
  cd ../certs
  pwd
  ls -ltr
fi

#rm ${domain}\.pem
#rm ${domain}-key\.pem

echo """
==================================================
  Add the following to host machine's /etc/hosts file:

  127.0.0.1 ${domain}
==================================================
"""

