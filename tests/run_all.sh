#!/bin/sh
dir=$(dirname $0)
cd $dir
mkdir -p result

php test.php unit | tee result/tests_unit.xml
php test.php functional | tee result/tests_functional.xml
exit $?
