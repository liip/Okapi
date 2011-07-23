#!/bin/sh
dir=$(dirname $0)
cd $dir
# change to root folder
cd ..

# make results folder
mkdir -p tests/result/clover/html/unit
mkdir -p tests/result/clover/html/functional

# clean results folder
rm -rf tests/result/*.xml
rm -rf tests/result/clover/*.xml
rm -rf tests/result/clover/html/unit/*
rm -rf tests/result/clover/html/functional/*

## run phpunit tests
# run functional suite:
phpunit \
    --log-junit=tests/result/functional.xml \
    --coverage-clover=tests/result/clover/functional.xml \
    --coverage-html=tests/result/clover/html/functional \
    tests/functional/
# run unit suite:
phpunit \
    --log-junit=tests/result/unit.xml \
    --coverage-clover=tests/result/clover/unit.xml \
    --coverage-html=tests/result/clover/html/unit \
    tests/unit/