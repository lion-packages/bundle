# Release Notes

## [v13.5.0](https://github.com/Sleon4/Lion-Framework/compare/v13.4.0...v13.5.0) (2023-03-22)

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