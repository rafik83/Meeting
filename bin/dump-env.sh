#!/bin/bash

# dump environement variables, since they're not available for user www-data when application is deployed
# this allows application to get values from root env
if [ -z "$KUBERNETES_PORT" ]
then
    echo "This script should be not be run manually"
else
    echo "VIMEET_DATABASE_URL=${VIMEET_DATABASE_URL}" > .env.prod.local
    echo "REDIS_URL=redis://${REDIS_HOST}:${REDIS_PORT}" >> .env.prod.local
    echo "CCIP_MODE=${CCIP_MODE}" >> .env.prod.local
    echo "CCIP_FORM_ACTION=${CCIP_FORM_ACTION}" >> .env.prod.local
    echo "CCIP_STORE_KEY=${CCIP_STORE_KEY}" >> .env.prod.local
    composer dump-env prod
fi
