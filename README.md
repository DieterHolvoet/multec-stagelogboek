# multec-stagelogboek
* PHP wrapper for the [internship logbook](https://internship.ehb.be/Multec) of Erasmushogeschool Brussel
* Class to fetch and convert [Harvest](https://www.getharvest.com) entries to Stagelogboek entries
* Simple one-pager for batch importing entries from the Harvest time tracker

## Installation
1. Clone the repository and put it on a (local) webserver.
2. Install [Composer](https://getcomposer.org/) and [Yarn](https://yarnpkg.com).
3. Run `composer install` on the root of the project to install the PHP dependencies.
4. Run `yarn install` on the root of the project to install the one-pager front-end dependencies.
5. Run `yarn build` on the root of the project to compile the JS and CSS for the one-pager, or run `yarn dev` to build and watch for changes.
6. Store your login credentials in an `.env` file in the root of the project (look at the `.env.example` file).
