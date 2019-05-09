# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- Block meeting slot and spot for VIMEET-1955: Scheduling meetings from requests unanimously accepted by linked sheets
- MV-173 - Check participants of linked sheets instead of only participants of the sheet
- MV-175 - Fix batch print sheets pdf
### Added
- VIMEET-1945 - Scan the badge of another participant and export contacts list
- VIMEET-1951 - Add contacts list

## [2.2.0] - 2019-04-26
### Changed
- VIMEET-1942 - Planning print bolded sheets titles
- VIMEET-1914 - Bold on sheet titles
### Added
- VIMEET-1950 - In back-office, all participants are assigned to meeting if option is activated.
- VIMEET-1931 - Unallow link if sheet has a scheduled meeting

## [2.1.0] - 2019-04-24
### Fixed
- Process all Sheets in catalog instead of all sheets with approved requests (fix https://sentry.io/organizations/elao/issues/996055540/)
### Added
- VIMEET-1955 - Scheduling meetings from requests unanimously accepted by linked sheets
- VIMEET-1938 - Can filter rooming list by type
- VIMEET-1944 - Unallow request meeting placement when linked sheets meeting already placed.

## [2.0.9] - 2019-04-17
### Changed
- VIMEET-1913 - No participants selection on meeting request when meetings are assigned to all participants

## [2.0.8] - 2019-04-17
### Fixed
- MV-167 - Fix invoice generator

## [2.0.7] - 2019-04-15
### Added
- VIMEET-1937 : Add Matomo tracking code in event extra parameter. Add privacy policy page with Matomo tracking opt-out.

## [2.0.6] - 2019-04-15
### Fixed
- MV-168 - Import LENI users ids with owners of sheets
