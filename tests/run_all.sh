#!/bin/sh
dir=$(dirname $0)
cd $dir
mkdir -p result

php test.php | tee result/tests.xml
exit $?
