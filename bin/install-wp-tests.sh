#!/usr/bin/env bash
# install-wp-tests.sh
# Sets up the WordPress testing environment for CI.
# Based on the standard WP plugin scaffold script.

if [ $# -lt 3 ]; then
    echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
    WP_BRANCH=${WP_VERSION%\-*}
    WP_TESTS_TAG="branches/$WP_BRANCH"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
    WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
    if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
        WP_TESTS_TAG="tags/${WP_VERSION%??}"
    else
        WP_TESTS_TAG="tags/$WP_VERSION"
    fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
    WP_TESTS_TAG="trunk"
else
    # Resolve latest stable version
    download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
    grep -o '"version":"[^"]*"' /tmp/wp-latest.json | head -1 | sed 's/"version":"//;s/"//' > /tmp/wp-latest.txt
    WP_VERSION=$(cat /tmp/wp-latest.txt)
    WP_TESTS_TAG="tags/$WP_VERSION"
fi

set -ex

install_wp() {
    if [ -d $WP_CORE_DIR ]; then
        return;
    fi

    mkdir -p $WP_CORE_DIR

    if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
        mkdir -p $TMPDIR/wordpress-nightly
        download https://wordpress.org/nightly-builds/wordpress-latest.zip $TMPDIR/wordpress-nightly/wordpress-nightly.zip
        unzip -q $TMPDIR/wordpress-nightly/wordpress-nightly.zip -d $TMPDIR/wordpress-nightly/
        mv $TMPDIR/wordpress-nightly/wordpress/* $WP_CORE_DIR
    else
        if [ $WP_VERSION == 'latest' ]; then
            local ARCHIVE_NAME='latest'
        elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+ ]]; then
            # https serves them regardless of the casing
            local ARCHIVE_NAME="wordpress-$WP_VERSION"
        fi
        download https://wordpress.org/${ARCHIVE_NAME}.zip $TMPDIR/wordpress.zip
        local WP_EXTRACT_DIR=$TMPDIR/wordpress-extract-$$
        unzip -q $TMPDIR/wordpress.zip -d $WP_EXTRACT_DIR/
        mv $WP_EXTRACT_DIR/wordpress/* $WP_CORE_DIR
        rm -rf $WP_EXTRACT_DIR
    fi

    download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
}

install_test_suite() {
    # Portable in-place argument for both GNU sed and Mac OSX sed
    if [[ $(uname -s) == 'Darwin' ]]; then
        local ioption='-i .bak'
    else
        local ioption='-i'
    fi

    # Set up testing suite if it doesn't yet exist
    if [ -d $WP_TESTS_DIR ]; then
        return;
    fi

    mkdir -p $WP_TESTS_DIR

    svn co --quiet --ignore-externals https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes
    svn co --quiet --ignore-externals https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data

    download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php $WP_TESTS_DIR/wp-tests-config.php
    # Remove all dev dependencies
    sed $ioption "s:dirname( dirname( __FILE__ ) ) . '/src':'/tmp/wordpress':" $WP_TESTS_DIR/wp-tests-config.php
    sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" $WP_TESTS_DIR/wp-tests-config.php
    sed $ioption "s/yourusernamehere/$DB_USER/" $WP_TESTS_DIR/wp-tests-config.php
    sed $ioption "s/yourpasswordhere/$DB_PASS/" $WP_TESTS_DIR/wp-tests-config.php
    sed $ioption "s|localhost|${DB_HOST}|" $WP_TESTS_DIR/wp-tests-config.php
}

install_db() {
    if [ ${SKIP_DB_CREATE} = "true" ]; then
        return
    fi

    # Parse DB_HOST for port or socket references
    local PARTS=(${DB_HOST//\// })
    local DB_HOSTNAME=${PARTS[0]};
    local DB_SOCK_OR_PORT=${PARTS[1]};
    local EXTRA=""

    if ! [ -z $DB_HOSTNAME ] ; then
        if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]*$') ]; then
            EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
        elif ! [ -z $DB_SOCK_OR_PORT ] ; then
            EXTRA=" --socket=$DB_SOCK_OR_PORT"
        elif ! [ -z $DB_HOSTNAME ] ; then
            EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
        fi
    fi

    # Create the database
    mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_wp
install_test_suite
install_db
