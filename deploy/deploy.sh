#!/bin/sh
SERVER="Aplication";
case $ENV in
    "production")
        PROJECT="aplication";
        DEST="/home/username/${PROJECT}";
       
    ;;
    "testing")
        PROJECT="aplication";
        DEST="/home/username/${PROJECT}";
    ;;
    *)
        echo "Can't deploy. ${ENV} is an invalid environment"
        exit 1
    ;;
esac
echo "INFO: Deploying ${PROJECT} into ${ENV}"
rsync -avrmR -e ssh --exclude='vendor' --exclude='.env' --exclude='composer.lock' --exclude='.git'  --exclude='.gitignore' --exclude='.vscode' . $SERVER:$DEST
echo "INFO: Deployed in ${DEST} into ${ENV}"
ssh $SERVER "cd ${DEST} && composer update && cp ${DEST}/env/${ENV}.env $DEST/.env"
echo "INFO: env copied and composer updated"