# Release Notes

## [v15.3.0](https://github.com/lion-packages/framework/compare/v15.2.0...v15.3.0) (2023-09-24)

### Added
- PSR standards have been applied to existing files
- options have been added to the test command to run specific tests
- added configuration to run test blocks from test command
- test has been added to check helpers in config
- added test to check StatusResponseEnum in Enums
- PSR standards have been applied for creating files in the new:test command
- PSR standards have been applied for creating files in the new:trait command
- PSR standards have been applied for creating files in the new:rule command
- PSR standards have been applied for creating files in the new:model command
- PSR standards have been applied for creating files in the new:middleware command
- PSR standards have been applied for creating files in the new:interface command
- PSR standards have been applied for creating files in the new:enum command
- PSR standards have been applied for creating files in the new:controller command
- PSR standards have been applied for creating files in the new:command command
- PSR standards have been applied for creating files in the new:capsule command
- PSR standards have been applied for creating files in the socket:new command
- PSR standards have been applied for creating files in the migrate:new command
- PSR standards have been applied for creating files in the db:seed command
- PSR standards have been applied for creating files in the db:factory command
- PSR standards have been applied for creating files in the db:rules command
- PSR standards have been applied for creating files in the db:capsule command
- PSR standards have been applied for creating files in the db:crud command

### Fixed
- corrected data type for cors configuration file

### Refactoring
- file name has been changed for exporting mysql databases
- changed http status codes for JWTMiddleware

## [v15.2.0](https://github.com/lion-packages/framework/compare/v15.1.1...v15.2.0) (2023-09-11)

### Changed
- middleware validation has been modified

## [v15.1.1](https://github.com/lion-packages/framework/compare/v15.1.0...v15.1.1) (2023-08-21)

### Changed
- the db:select command has been validated to select data from an entity
- the db:columns command has been validated to select data from an entity
- updated docker-compose configuration

### Fixed
- fixed reading folders to run migrations

## [v15.1.0](https://github.com/lion-packages/framework/compare/v15.0.1...v15.1.0) (2023-08-08)

### Added
- added command to create vite projects
- command has been added to generate vite project logs

### Changed
- commands have been removed for the resources section
- data reading has been validated to generate migrations

### Refactoring
- resources have been renamed to vite

## [v15.0.1](https://github.com/lion-packages/framework/compare/v15.0.0...v15.0.1) (2023-08-02)

### Changed
- docker config updated

### Fixed
- fixed configuration validation for resources

## [v15.0.0](https://github.com/lion-packages/framework/compare/v14.29.1...v15.0.0) (2023-08-01)

### Added
- command has been added to generate resource logs
- added command to generate socket logs
- support has been given to create resources with vite.js
- added command to install dependencies with npm for resources
- added command to uninstall dependencies with npm for a vite type resource
- added command to update dependencies with npm for a resource of type vite
- added command to run vite project with build

### Changed
- configuration in Dockerfile has been updated
- resource initialization has been modified through the resources configuration file

### Refactoring
- rules have been modified in editorconfig

## [v14.29.1](https://github.com/lion-packages/framework/compare/v14.29.0...v14.29.1) (2023-07-27)

### Added
- echo function has been added to display data from the terminal with output and store the string in logger
- multiple requests with the same name for collections in postman have been supported

### Changed
- output to run migrations has been modified
- lion/route library v8.7.0 has been updated
- resource has been updated to display available routes
- json output format has been modified to generate postman collections

### Fixed
- fixed validation to add generated elements in migrations
- validation to generate logs by system functions has been corrected
- fixed generation of migrations

## [v14.29.0](https://github.com/lion-packages/framework/compare/v14.28.1...v14.29.0) (2023-07-20)

### Added
- added trait to generate keys for AES

### Changed
- output format for rules have been modified
- RSA route settings have been updated
- resource responses have been modified
- initialization in lion file has been modified

### Refactoring
- command has been renamed SSHFileCommand SHFileCommand

## [v14.28.1](https://github.com/lion-packages/framework/compare/v14.28.0...v14.28.1) (2023-07-15)

### Added
- command to export databases (db:export) has been added
- migrations have been added

### Changed
- routes have been validated to display routes from the console
- rules have been updated
- query has been validated to read tables from command (db:select)
- query has been validated to read columns from command (db:columns)
- output format has been modified to create migrations
- login has been validated

### Fixed
- objects have been validated to generate crud (db:all-crud)

### Refactoring
- environment variables have been updated

## [v14.28.0](https://github.com/lion-packages/framework/compare/v14.27.0...v14.28.0) (2023-07-11)

### Changed
- lionsql library has been migrated to liondatabase
- user authentication has been validated
- middleware structure has been modified in config/

### Refactoring
- the default http response code for helper error has been modified

## [v14.27.0](https://github.com/lion-packages/framework/compare/v14.26.0...v14.27.0) (2023-07-10)

### Added
- support has been added to display parameters for HTTP routes with the ANY method
- trait session has been added to modify and create sessions from the Kernel (http)
- trait Index has been added to initialize modules to the index

### Changed
- existence of http method for rules has been validated

### Refactoring
- environment variables (env) have been updated
- default ports have been updated to execute resources
- trait HttpTrait has been renamed to Http

## [v14.26.0](https://github.com/lion-packages/framework/compare/v14.25.0...v14.26.0) (2023-06-29)

### Added
- added URI when generating logs in storage/logs/
- resource has been added to display the available http routes

## [v14.25.0](https://github.com/lion-packages/framework/compare/v14.24.1...v14.25.0) (2023-06-27)

### Added
- rules have been added

### Changed
- lion/mailer library v5.1.0 has been updated
- lion/files library v4.11.0 has been updated
- lion/request library v5.4.0 has been updated
- response helpers have been updated
- output format has been modified to display web routes (route:list)
- rule generation has been validated so as not to generate duplicate rules (foreign) in commands (db:rules)
- added option to command (token:jwt) to allow selecting a path where public and private keys can be located

## [v14.24.1](https://github.com/lion-packages/framework/compare/v14.24.0...v14.24.1) (2023-06-25)

### Added
- the types of migrations have been classified when they are created (migrate:new)
- command has been modified to run all migrations by type (migrate:fresh)
- default migrations have been added
- trait has been added to display colored text for commands

### Changed
- output format for commands has been modified
- docker config updated

### Fixed
- format to generate migrations and add columns with foreign keys has been corrected

## [v14.24.0](https://github.com/lion-packages/framework/compare/v14.23.0...v14.24.0) (2023-06-25)

### Added
- resource has been added to register users
- added resource to authenticate users

### Changed
- lion/sql library has been updated v8.7.1

## [v14.23.0](https://github.com/lion-packages/framework/compare/v14.22.0...v14.23.0) (2023-06-24)

### Added
- resource design has been modified to work with the cli

### Changed
- docker configuration has been updated

## [v14.22.0](https://github.com/lion-packages/framework/compare/v14.21.0...v14.22.0) (2023-06-22)

### Added
- added command to generate resources
- command to execute resources has been added
- a resource has been added to dynamically access the cli

## [v14.21.0](https://github.com/lion-packages/framework/compare/v14.20.0...v14.21.0) (2023-06-21)

### Added
- rules format has been modified allowing the use of dynamic parameters
- command to generate interfaces has been added
- added class to encrypt and decrypt with RSA

### Changed
- docker config updated
- format for creating sockets has been updated

## [v14.20.0](https://github.com/lion-packages/framework/compare/v14.19.0...v14.20.0) (2023-06-20)

### Added
- added supervisord configuration to run local server services and sockets
- added helper session to modify session variables with PHP
- middleware configuration has been added to the config
- support for rules with dynamic parameters has been added

### Changed
- format to generate sockets has been modified
- execution of sockets commands has been modified
- configuration in docker has been modified

### Refactoring
- class has been renamed to generate sockets
- class has been renamed to execute sockets

## [v14.19.0](https://github.com/lion-packages/framework/compare/v14.18.0...v14.19.0) (2023-06-19)

### Added
- added functions to make session variables
- middleware added to config

### Changed
- configuration in docker has been updated

## [v14.18.0](https://github.com/lion-packages/framework/compare/v14.17.1...v14.18.0) (2023-06-18)

### Added
- added format support for columns of type (blob, varbinary) to generate migrations

### Changed
- query has been modified to execute fresh migrations
- table format has been validated to generate crud
- query has been modified to read columns in command (db:columns)
- query query has been modified to read tables in command (db:select)
- configuration in dockerfile has been updated
- environment variables have been updated

## [v14.17.1](https://github.com/lion-packages/framework/compare/v14.17.0...v14.17.1) (2023-06-15)

### Fixed
- function call from trait has been fixed

## [v14.17.0](https://github.com/lion-packages/framework/compare/v14.16.0...v14.17.0) (2023-06-14)

### Changed
- output format for commands has been modified

## [v14.16.0](https://github.com/lion-packages/framework/compare/v14.15.0...v14.16.0) (2023-06-13)

### Changed
- format has been modified to insert the name of the connection in the generated migrations
- view service and procedures to generate migrations have been removed

## [v14.15.0](https://github.com/lion-packages/framework/compare/v14.14.0...v14.15.0) (2023-06-12)

### Added
- added command function in App\Console\Kernel to run lion commands
- added kernel constant to App\Console\Kernel class to run commands
- command has been added to see the list of available email accounts
- command has been added to generate migrations of the connections to available databases
- command has been added to generate all the rules of all the entities

### Changed
- creation format for new migrations has been modified

### Refactoring
- the execute function of the App\Console\Kernel class has been modified to return an array with responses from the terminal
- command renamed App\Console\Framework\Migrations\RunMigrationsCommand to App\Console\Framework\Migrations\FreshMigrationsCommand

## [v14.14.0](https://github.com/lion-packages/framework/compare/v14.13.0...v14.14.0) (2023-06-09)

### Added
- new default migration has been added
- added command to read entities from databases
- added command to read entity columns

### Changed
- format to create commands has been modified

## [v14.13.0](https://github.com/lion-packages/framework/compare/v14.12.0...v14.13.0) (2023-06-08)

### Added
- command to generate migrations has been added
- added command to run all migrations
- added default migrations

### Changed
- commands have been updated with their respective queries

### Refactoring
- command to generate keys with RSA has been renamed
- command to generate keys with AES has been renamed

## [v14.12.0](https://github.com/lion-packages/framework/compare/v14.11.0...v14.12.0) (2023-06-06)

### Added
- commands have been added to generate rules with the properties of an entity
- call has been made to generate rules for an entity from the command (db:crud)
- added information when running local server

## [v14.11.0](https://github.com/lion-packages/framework/compare/v14.10.1...v14.11.0) (2023-06-05)

### Added
- command to generate CRUD functions of an entity has been added
- added command to generate all system crud

### Changed 
- default connection has been validated to display the available connections through the terminal (db:show)
- table format has been modified to display the available web http routes

## [v14.10.1](https://github.com/lion-packages/framework/compare/v14.10.0...v14.10.1) (2023-06-02)

### Changed
- lion/sql library has been updated v8.1.1

### Fixed
- fixed rule validation

## [v14.10.0](https://github.com/lion-packages/framework/compare/v14.9.0...v14.10.0) (2023-06-01)

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

## [v14.9.0](https://github.com/lion-packages/framework/compare/v14.8.0...v14.9.0) (2023-05-30)

### Added
- added new helper to verify and get JWT token
- the execute function has been added in the kernel to execute commands

### Changed
- lion/files library has been updated to v4.10.0
- nesbot/carbon library has been updated to v2.67.0

### Fixed
- fixed value type that returns helper isError and isSuccess to bool

## [v14.8.0](https://github.com/lion-packages/framework/compare/v14.7.0...v14.8.0) (2023-05-27)

### Changed
- lion/route library has been updated to v8.4.0
- controller class format has been modified

## [v14.7.0](https://github.com/lion-packages/framework/compare/v14.6.0...v14.7.0) (2023-05-25)

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

## [v14.6.0](https://github.com/lion-packages/framework/compare/v14.5.0...v14.6.0) (2023-05-17)

### Added
- command list and socket commands have been relocated

### Changed
- command to create sockets have been relocated
- function has been validated to refresh jwt token
- dockerfile has been updated
- updated path to create commands

## [v14.5.0](https://github.com/lion-packages/framework/compare/v14.4.0...v14.5.0) (2023-05-15)

### Added
- command to create rules and add properties has been modified
- added value and disabled properties to export data in postman collections

## [v14.4.0](https://github.com/lion-packages/framework/compare/v14.3.2...v14.4.0) (2023-05-14)

### Added
- docker files have been updated to implement cron tasks
- command to create files with sh extension has been added

## [v14.3.2](https://github.com/lion-packages/framework/compare/v14.3.1...v14.3.2) (2023-05-12)

### Changed
- updated lion/sql library to v8.0.3

### Fixed
- postman collections has been validated

## [v14.3.1](https://github.com/lion-packages/framework/compare/v14.3.0...v14.3.1) (2023-05-10)

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

## [v14.3.0](https://github.com/lion-packages/framework/compare/v14.2.0...v14.3.0) (2023-05-06)

### Added
- session controller has been created to refresh JWT token
- added trait to generate fake data in factory

### Changed
- validation with enum values have been added
- sql library has been updated to v8.0.2

## [v14.2.0](https://github.com/lion-packages/framework/compare/v14.1.0...v14.2.0) (2023-05-05)

### Added
- desc property is added to assign a description in postman properties

### Changed
- lion/sql library has been updated

### Fixed
- import for connection to databases has been corrected
- fixed database import in different commands

## [v14.1.0](https://github.com/lion-packages/framework/compare/v14.0.0...v14.1.0) (2023-05-03)

### Changed
- .env properties have been modified
- Dockerfile has been updated

## [v14.0.0](https://github.com/lion-packages/framework/compare/v13.14.0...v14.0.0) (2023-05-01)

### Added
- added helper to convert data to json
- command to modify host when starting local server has been modified
- trait SoftDeletes has been added to perform temporary data deletion
- necessary files for docker containers have been added

## [v13.14.0](https://github.com/lion-packages/framework/compare/v13.13.0...v13.14.0) (2023-04-25)

### Added
- the visualization of middleware available for each route is added

### Changed
- added properties for editorconfig
- added properties in gitignore

## [v13.13.0](https://github.com/lion-packages/framework/compare/v13.12.0...v13.13.0) (2023-04-24)

### Changed
- added database visualization when mapping databases in general
- PSR4 format has been modified to create capsule classes

## [v13.12.0](https://github.com/lion-packages/framework/compare/v13.11.0...v13.12.0) (2023-04-18)

### Added
- property has been added to the command to create controllers

### Changed
- data properties have been removed for rule response

## [v13.11.0](https://github.com/lion-packages/framework/compare/v13.10.0...v13.11.0) (2023-04-16)

### Added
- added option to create models when creating controllers
- added table to display query data for seeders

### Changed
- seeders and factories format has been standardized
- property has been modified to set the current database connection

## [v13.10.0](https://github.com/lion-packages/framework/compare/v13.9.0...v13.10.0) (2023-04-14)

### Added
- dynamic parameters have been added to export collections in postman
- added header to set a default time zone

### Changed
- command to create rules has been modified
- add default value for environment variables

## [v13.9.0](https://github.com/lion-packages/framework/compare/v13.8.1...v13.9.0) (2023-04-10)

### Added
- added property to set default time zone

### Changed
- capsule class properties have been normalized
- export for postman collections has been normalized

## [v13.8.1](https://github.com/lion-packages/framework/compare/v13.8.0...v13.8.1) (2023-04-10)

### Added
- added environment variable

### Fixed
- fixed capsule class generation for multiple connections

### Changed
- normalized format for naming classes and methods has been corrected

## [v13.8.0](https://github.com/lion-packages/framework/compare/v13.7.0...v13.8.0) (2023-04-09)

### Added
- the config section has been created to create configuration files for the framework

### Fixed
- fixed route export for postman

## [v13.7.0](https://github.com/lion-packages/framework/compare/v13.6.0...v13.7.0) (2023-04-06)

### Changed
- the export of collections to postman has been restructured

## [v13.6.0](https://github.com/lion-packages/framework/compare/v13.5.0...v13.6.0) (2023-04-05)

### Added
- add command to create enums
- added log recording for routes and database
- the first tree format for the Postman collection has been organized
- trait has been created to make basic collections to import into postman

### Changed
- basic information of the 'route:postman' command is added
- properties have been hidden to export the collection in postman

## [v13.5.0](https://github.com/lion-packages/framework/compare/v13.4.0...v13.5.0) (2023-03-27)

### Changed
- rules response format is modified where properties are added to obtain required validation responses for a property
- sql library updated

## [v13.4.0](https://github.com/lion-packages/framework/compare/v13.3.0...v13.4.0) (2023-03-22)

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

## [v13.3.0](https://github.com/lion-packages/framework/compare/v13.2.0...v13.3.0) (2023-03-17)

### Added
- property was added to the response of rules to determine the attribute with error
- added logger to read rule errors

### Changed
- object was removed to display table
- command has been modified to display if it is a route or a request route
