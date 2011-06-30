#!/bin/sh

cd $(dirname $0)

METHOD=git
if [ "$1" = "--http" ]; then
    METHOD=http
fi

# initialization
if [ "$2" = "--reinstall" ]; then
    rm -rf vendor
fi

mkdir -p vendor && cd vendor

#
# @param destination directory (e.g. "doctrine")
# @param URL of the git remote (e.g. git://github.com/doctrine/doctrine2.git)
# @param revision to point the head (e.g. origin/HEAD)
#
install_git()
{
    INSTALL_DIR=$1
    SOURCE_URL=$2
    REV=$3

    if [ -z $REV ]; then
        REV=origin/HEAD
    fi

    if [ ! -d $INSTALL_DIR ]; then
        git clone $SOURCE_URL $INSTALL_DIR
    fi

    cd $INSTALL_DIR
    git fetch origin
    git reset --hard $REV
    cd ..
}

# Symfony
install_git symfony $METHOD://github.com/symfony/symfony.git

# Doctrine
install_git doctrine-dbal $METHOD://github.com/doctrine/dbal.git 2.1.0RC2
install_git doctrine-common $METHOD://github.com/doctrine/common.git 2.1.0RC2
