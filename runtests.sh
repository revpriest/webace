#!/bin/bash

export APPLICATION_ENV="development"
cd tests
phpunit
cd ..


