# Release Notes

## [v14.11.0](https://github.com/Sleon4/Lion-Framework/compare/v14.10.1...v14.11.0) (2023-06-06)

### Added
- command to generate CRUD functions of an entity has been added
- added command to generate all system crud

### Changed 
- default connection has been validated to display the available connections through the terminal (db:show)
- table format has been modified to display the available web http routes

## [v14.10.1](https://github.com/Sleon4/Lion-Framework/compare/v14.10.0...v14.10.1) (2023-06-02)

### Changed
- lion/sql library has been updated v8.1.1

### Fixed
- fixed rule validation

## [v14.10.0](https://github.com/Sleon4/Lion-Framework/compare/v14.9.0...v14.10.0) (2023-06-01)

### Added
- email has been added to the config to configure multiple email accounts
- environment variables have been added
- unit test has been added for emails
- constant has been added as a helper for the Str class of the LionHelpers library
- constant has been added as a helper for the Arr class of the LionHelpers library
- added cors in config to configure headers in the framework

### Changed
- lion/helpers library has been updated to v2.3.0
- lion/security library has been updated to v6.12.0
- lion/mailer library has been updated to v5.0.0
- lion/sql library has been updated v8.1.0
- command format to create test has been modified
- format has been modified to create controllers in command

## [v14.9.0](https://github.com/Sleon4/Lion-Framework/compare/v14.8.0...v14.9.0) (2023-05-30)

### Added
- added new helper to verify and get JWT token
- the execute function has been added in the kernel to execute commands

### Changed
- lion/files library has been updated to v4.10.0
- nesbot/carbon library has been updated to v2.67.0

### Fixed
- fixed value type that returns helper isError and isSuccess to bool

## [v14.8.0](https://github.com/Sleon4/Lion-Framework/compare/v14.7.0...v14.8.0) (2023-05-27)

### Changed
- lion/route library has been updated to v8.4.0
- controller class format has been modified

## [v14.7.0](https://github.com/Sleon4/Lion-Framework/compare/v14.6.0...v14.7.0) (2023-05-25)

### Added
- middleware has been added to check if the jwt exists
- middleware has been added to check if the jwt exists and validate it without digital signature
- added helper to check if a response object is of type error
- added helper to check if a response object is successful
- command has been added to display basic information of the framework
- added command to generate keys for AES encryption
- basic information for commands has been added

### Changed
- class has been renamed to JWTMiddleware
- moved helpers to config
- added default null value for helpers (success, error, warning, info)
- list of commands and sockets has been modified
- camelCase format has been added for capsule method in generated capsule classes
- added camelCase format for model objects generated from controller command
- format of functions for controllers have been modified
- format of functions for model have been modified
- format of for enums have been modified
- updated lion/mailer library to v4.3.0
- environment variables have been updated
- updated validation to refresh JWT token
- command format to initialize local server has been modified

## [v14.6.0](https://github.com/Sleon4/Lion-Framework/compare/v14.5.0...v14.6.0) (2023-05-17)

### Added
- command list and socket commands have been relocated

### Changed
- command to create sockets have been relocated
- function has been validated to refresh jwt token
- dockerfile has been updated
- updated path to create commands

## [v14.5.0](https://github.com/Sleon4/Lion-Framework/compare/v14.4.0...v14.5.0) (2023-05-15)

### Added
- command to create rules and add properties has been modified
- added value and disabled properties to export data in postman collections

## [v14.4.0](https://github.com/Sleon4/Lion-Framework/compare/v14.3.2...v14.4.0) (2023-05-14)

### Added
- docker files have been updated to implement cron tasks
- command to create files with sh extension has been added

## [v14.3.2](https://github.com/Sleon4/Lion-Framework/compare/v14.3.1...v14.3.2) (2023-05-12)

### Changed
- updated lion/sql library to v8.0.3

### Fixed
- postman collections has been validated

## [v14.3.1](https://github.com/Sleon4/Lion-Framework/compare/v14.3.0...v14.3.1) (2023-05-10)

### Added
- values method is added in StatusEnum to obtain the available statuses
- function has been added to check if an object is of type error
- added http PATCH protocol for exporting in postman collections
- added enum as import to controller command

### Changed
- headers have been modified
- updated lion/helpers library to v2.2.0
- updated lion/sql library to v8.0.3

### Fixed
- fixed json format for postman collections
- commands that do not return strings have been modified
- table format to generate capsule classes has been corrected
- classes and attributes have been normalized for capsule classes
- fixed postman collection export

## [v14.3.0](https://github.com/Sleon4/Lion-Framework/compare/v14.2.0...v14.3.0) (2023-05-06)

### Added
- session controller has been created to refresh JWT token
- added trait to generate fake data in factory

### Changed
- validation with enum values have been added
- sql library has been updated to v8.0.2

## [v14.2.0](https://github.com/Sleon4/Lion-Framework/compare/v14.1.0...v14.2.0) (2023-05-05)

### Added
- desc property is added to assign a description in postman properties

### Changed
- lion/sql library has been updated

### Fixed
- import for connection to databases has been corrected
- fixed database import in different commands

## [v14.1.0](https://github.com/Sleon4/Lion-Framework/compare/v14.0.0...v14.1.0) (2023-05-03)

### Changed
- .env properties have been modified
- Dockerfile has been updated

## [v14.0.0](https://github.com/Sleon4/Lion-Framework/compare/v13.14.0...v14.0.0) (2023-05-01)

### Added
- added helper to convert data to json
- command to modify host when starting local server has been modified
- trait SoftDeletes has been added to perform temporary data deletion
- necessary files for docker containers have been added

## [v13.14.0](https://github.com/Sleon4/Lion-Framework/compare/v13.13.0...v13.14.0) (2023-04-25)

### Added
- the visualization of middleware available for each route is added

### Changed
- added properties for editorconfig
- added properties in gitignore

## [v13.13.0](https://github.com/Sleon4/Lion-Framework/compare/v13.12.0...v13.13.0) (2023-04-24)

### Changed
- added database visualization when mapping databases in general
- PSR4 format has been modified to create capsule classes

## [v13.12.0](https://github.com/Sleon4/Lion-Framework/compare/v13.11.0...v13.12.0) (2023-04-18)

### Added
- property has been added to the command to create controllers

### Changed
- data properties have been removed for rule response

## [v13.11.0](https://github.com/Sleon4/Lion-Framework/compare/v13.10.0...v13.11.0) (2023-04-16)

### Added
- added option to create models when creating controllers
- added table to display query data for seeders

### Changed
- seeders and factories format has been standardized
- property has been modified to set the current database connection

## [v13.10.0](https://github.com/Sleon4/Lion-Framework/compare/v13.9.0...v13.10.0) (2023-04-14)

### Added
- dynamic parameters have been added to export collections in postman
- added header to set a default time zone

### Changed
- command to create rules has been modified
- add default value for environment variables

## [v13.9.0](https://github.com/Sleon4/Lion-Framework/compare/v13.8.1...v13.9.0) (2023-04-10)

### Added
- added property to set default time zone

### Changed
- capsule class properties have been normalized
- export for postman collections has been normalized

## [v13.8.1](https://github.com/Sleon4/Lion-Framework/compare/v13.8.0...v13.8.1) (2023-04-10)

### Added
- added environment variable

### Fixed
- fixed capsule class generation for multiple connections

### Changed
- normalized format for naming classes and methods has been corrected

## [v13.8.0](https://github.com/Sleon4/Lion-Framework/compare/v13.7.0...v13.8.0) (2023-04-09)

### Added
- the config section has been created to create configuration files for the framework

### Fixed
- fixed route export for postman

## [v13.7.0](https://github.com/Sleon4/Lion-Framework/compare/v13.6.0...v13.7.0) (2023-04-06)

### Changed
- the export of collections to postman has been restructured

## [v13.6.0](https://github.com/Sleon4/Lion-Framework/compare/v13.5.0...v13.6.0) (2023-04-05)

### Added
- add command to create enums
- added log recording for routes and database
- the first tree format for the Postman collection has been organized
- trait has been created to make basic collections to import into postman

### Changed
- basic information of the 'route:postman' command is added
- properties have been hidden to export the collection in postman

## [v13.5.0](https://github.com/Sleon4/Lion-Framework/compare/v13.4.0...v13.5.0) (2023-03-27)

### Changed
- rules response format is modified where properties are added to obtain required validation responses for a property
- sql library updated

## [v13.4.0](https://github.com/Sleon4/Lion-Framework/compare/v13.3.0...v13.4.0) (2023-03-22)

### Added
- helper has been added to make http requests to get xml
- helper has been added to return a response of type success
- helper has been added to return a response of type error
- helper has been added to return a response of type warning
- helper has been added to return a response of type info

### Fixed
- function that does not exist to generate keys with RSA has been corrected

### Changed
- changed directory routing for helper storage_path
- property has been removed in .env.example
- function called to access storage

## [v13.3.0](https://github.com/Sleon4/Lion-Framework/compare/v13.2.0...v13.3.0) (2023-03-17)

### Added
- property was added to the response of rules to determine the attribute with error
- added logger to read rule errors

### Changed
- object was removed to display table
- command has been modified to display if it is a route or a request route
