# Release Notes

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