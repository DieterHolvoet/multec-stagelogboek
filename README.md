# multec-stagelogboek
* PHP wrapper for the [internship logbook](https://internship.ehb.be/Multec) of Erasmushogeschool Brussel
* Class to fetch and convert [Harvest](https://www.getharvest.com) entries to Stagelogboek entries
* Simple one-pager for batch importing entries from the Harvest time tracker

## Installation
1. Clone the repository and put it on a (local) webserver
2. Run `composer install` on the root of the project to install the PHP dependencies
3. Run `npm install` on the root of the project to install the one-pager front-end dependencies
4. Store your login credentials in an `.env` file in the root of the project (look at the `.env.example` file)
