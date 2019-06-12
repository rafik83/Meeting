# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- VIMEET-1995 - add products in cart counter
- VIMEET-1990 - Administrate tip message conditions
- VIMEET-1940 - Save rooming list filters
- VIMEET-1250 - Sheet filter by emailing

## [2.8.0] - 2019-06-05
### Fixed
- MV-183 - Add event timezone to rooming list assigning dates

## [2.7.0] - 2019-05-29
### Added
- VIMEET-1929 - Remove filter without result
- VIMEET-1904 - Add message to meeting diff
- VIMEET-1967 - Package can be required by type
- VIMEET-1968 - Payment can be required by type
- VIMEET-1965 - Must validate package
- VIMEET-1980 - Must validate transaction
- VIMEET-1871 - As an admin, add promotion code to order
- VIMEET-1899 - Add rooming list sheet state filter
- VIMEET-791 - Display conditional objective filter
- VIMEET-1874 - Can participant scan in Product option
- VIMEET-1972 - Display scan and contacts buttons by Product option

## [2.6.0] - 2019-05-16
### Added
- VIMEET-1956 - Batch action to send many invoices in one pdf

## [2.5.0] - 2019-05-15
### Added
- Hotfix VIMEET-1946 - Contacts and Scan buttons conditions
- VIMEET-1892 - Rooming export new colums
- VIMEET-1964 - Sheet export owner new columns

## [2.4.0] - 2019-05-14
### Fixed
- MV-155 - Cancel sheet orders when the sheet is disabled
- MV-175 - Fix batch print sheets pdf
- MV-176 - Update events date with different timezone
- MV-179 - Fix sheet title in planner sheet satisfaction view
### Added
- VIMEET-1957 - Evaluate and comment a contact
- VIMEET-1958 - Evaluate and comment in contact export
- VIMEET-1952 - Participants can scan according type parameter
- VIMEET-1946 - Sheet submenu buttons

## [2.3.0] - 2019-05-09
### Fixed
- Block meeting slot and spot for VIMEET-1955: Scheduling meetings from requests unanimously accepted by linked sheets
- MV-173 - Check participants of linked sheets instead of only participants of the sheet
- MV-174 - Prioritize requests with meeting to avoid deduplication before generating data for planner
### Added
- VIMEET-1945 - Scan the badge of another participant and export contacts list
- VIMEET-1951 - Add contacts list
- VIMEET-1939 - Sheet state in rooming list

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
