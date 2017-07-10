#!/bin/bash

if [ $# -eq 0 ]; then
    printf "\nUsage: ./generate.sh http://your-local-trip.dev"
else
    
printf "\n\e[0;32mGetting the data...\e[0;37m\n\n"

curl https://raw.githubusercontent.com/johan/world.geo.json/master/countries.geo.json > data/countries.json

curl https://raw.githubusercontent.com/datasets/country-codes/master/data/country-codes.csv > data/codes.csv

curl $1/api/destinations/data > data/destinations_data.json

printf "\n\e[0;32mGenerating the dots for the Dotmap component...\e[0;37m"
printf "\nThe script will take around 10 minutes to run\n\n"

node generate_dots.js > ../config/dots.php

printf "\n\e[0;32mDone\e[0;37m\n\n"

fi