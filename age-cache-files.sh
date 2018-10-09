#!/bin/bash

# Taken from: http://www.ahowto.net/linux/bash-script-delete-files-older-than-specified-time

TEMPLATE_FILES="*.php"
CACHE_FILES="*.url"

if [ "$1" != "dev" -a "$1" != "prod" ]; then
    echo -e '\nUSAGE:'
    echo -e '\tage-cache-files.sh dev|prod\n'
    exit;
fi

let "TPL_EXPIRETIME=24*60*60"                       # expire time in seconds (1 day)
if [ "$1" == "dev" ]; then
   TPL_DIR=/home/perry/links/pic/d/tmp/               # target directory where we should do some cleanup
   CAC_DIR=/home/perry/links/pic/d/tmp/cache/
   let "CAC_EXPIRETIME=24*60*60"                       # expire time in seconds (1 day)
else
   TPL_DIR=/home/perry/links/PROD_pic/d/tmp/                      # target directory where we should do some cleanup
   CAC_DIR=/home/perry/links/PROD_pic/d/tmp/cache/
   let "CAC_EXPIRETIME=7*24*60*60"                     # expire time in seconds (7 days)
fi

shopt -s nullglob                               # suppress "not found" message



cd $TPL_DIR                                         # change current working directory to target directory

for f in $TEMPLATE_FILES
do
    NOW=`/bin/date +%s`                          # get current time
    FCTIME=`/usr/bin/stat -c %Y ${f}`            # get file last modification time
    let "AGE=$NOW-$FCTIME"
    if [[ $AGE -gt $TPL_EXPIRETIME ]] ; then
        echo -e "removing tpl file [{$f}]"
        /bin/rm -f $f                             # this file age is more than the EXPIRETIME above, we can delete it
    fi
done



cd $CAC_DIR                                         # change current working directory to target directory

for f in $CACHE_FILES
do
    NOW=`/bin/date +%s`                          # get current time
    FCTIME=`/usr/bin/stat -c %Y ${f}`            # get file last modification time
    let "AGE=$NOW-$FCTIME"
    if [[ $AGE -gt $CAC_EXPIRETIME ]] ; then
        echo -e "removing cache file [{$f}]"
        /bin/rm -f $f                             # this file age is more than the EXPIRETIME above, we can delete it
    fi
done