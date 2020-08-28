# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- VIMEET-2106 - The speaking user video is maximized based on audio level
- Deploy: DB migration without interaction

## [2.43.0] - 2020-08-27
### Added
- VIMEET-2187 - Invisible mode for webinar speaker
- VIMEET-2174 - Do not ask a new password when user is logged with a token

## [2.42.0] - 2020-07-31
### Added
- VIMEET-2168 - Video object on sheet template
- Add fly system and google cloud storage adapter
- VIMEET-2173 - Add option submit validation sheet
- VIMEET-2170 - Upload Video on sheet

## [2.41.0] - 2020-07-30
### Fixed
- Hotfix - allow text/* on csv participant import

## [2.40.0] - 2020-07-30
### Added
- VIMEET-2159 - Add option sidebar webinar

### Fixed
- MV-247 - Do not show back link on registration pages when type is hidden
- Hotfix - Profile avatar missing styles
- MV-246 - Order option without group data
- MV-228 - Fix missing bullet points in registration path forms
- Add missing translation key for available priority request

### Updated
- MV-229 - Show cancel button when webinar has started

## [2.39.0] - 2020-07-24
### Added
- VIMEET 2086 - Webinar video available available even when the happening is over
- VIMEET-2155 - Password is required to change email in user account
- VIMEET-2157 - Save import mapping

### Updated
- Update SncRedisBundle to 3.2.3

### Fixed
- VIMEET-2154 - securize impersonation
- MV-239 - Fix batch action for paged filtered results, for all results selection
- Hotfix - meeting slot duration in admin
- Index users on mail change

## [2.38.0] - 2020-07-22
### Added
- VIMEET-2200 - Allow more planning than participant

## [2.37.0] - 2020-07-06
### Added
- VIMEET-2100 - Add composer.phar version 1.10.6 to the project

## [2.36.0] - 2020-07-03
### Updated
- Suppression de la librairie elao/form-bundle pour ramener sa logique en interne

### Fixed
- MV-242 - Fix meeting / contact evaluations on event dashboard
- MV-243 - Add media link in sheet pdf printing

### Added
- VIMEET-2149 - Change Admin password algorithm to argon2i and increase length

## [2.35.1] - 2020-06-30
### Fixed
- Hotfix - Get / export many happening questions instead of one or null

## [2.35.0] - 2020-06-30
### Added
- VIMEET-2171 - Add participant to meeting when accessing to a videoconference meeting
- VIMEET 2110 - Add support to broadcast live video in webinar, using an iframe
- VIMEET-2126 - Add Export all participants of sheet met
- VIMEET-2126 - Hide participants phone / email in contact export if evaluations are below a user-defined floor
- VIMEET-2153 - secure cookies

## [2.34.0] - 2020-06-25
### Added
- MV-240 - Asynchronous export of happening participants

## [2.33.0] - 2020-06-22
### Added
- VIMEET-2142 - Import multi sheet for user
- VIMEET-2131 - Add datetime when user connects to a webinar (like a scan in conference)
- VIMEET-2158 - Webinar fixes and improvement
- VIMEET-2144 - Webinar is always open to speaker

## [2.32.0] - 2020-06-18
### Added
- VIMEET-2141 - Webinar questions
- VIMEET-2102 - Evaluate visio meeting
- VIMEET-2128 - Show participant name on visio
- VIMEET-2140 - Screen share without feedback
- VIMEET-2150 - Block account in admin login after 5 failed attempts

### Fixed
- MV-236 - Add unavailability with a different timezone
- Add number validator for priority meeting request number
- Fix happening speakers display in program page
- VIMEET-2148 - Restrict PDF access

### Upgraded
- Update Sensio Framework Extra Bundle to 5.4.1

## [2.31.0] - 2020-06-01
### Added
- VIMEET-2124 - Share a video in a webinar
- VIMEET-2139 - Do not hide participants stream; maximize screen or video sharing

## [2.30.0] - 2020-05-29
### Fixed
- MV-237 - Check user imported and no password in register path

## [2.29.0] - 2020-05-25
### Added
- VIMEET-2117 - Webinar audio/video settings
- VIMEET-2088 - Sort sheet participants
- VIMEET-2101 - Add end sound, image and message to visio meeting
- VIMEET-2105 - Interactive webinar

## [2.28.0] - 2020-05-18
### Added
- VIMEET-2095 - Add image header to visio meeting

## [2.27.0] - 2020-05-15
### Added
- VIMEET-2096 - Add image header to webinar
- VIMEET-2050 - Can define an image to the header notification
- VIMEET-2118 - Add visio test menu button
### Fixed
- MV-230 - Webinar and counter fixes

## [2.26.0] - 2020-05-05
### Added
- VIMEET-2090 - Improve video conference meeting UX
- VIMEET-2078 - Keywords search from Sheet Query Builder
- VIMEET-2067 - Visio settings
### Fixed
- MV-220 - Fix logo error when ordering logo in the purchase tunnel
- MV-232 - Participant can access to all own sheet video meeting
- MV-234 - Fix sheet / participant export with dates filters
- Hotfix - Avoid pending job to be rescheduled

## [2.25.0] - 2020-03-30
### Added
- VIMEET-2025 - Add order date in the order export
- VIMEET-2060 - Assist to a webinar as a speaker or a viewer. Screensharing for speaker; Chat for both
### Fixed
- VIMEET-2071 - Fix euro position always at the same side
- VIMEET-2094 - Convert nomenclature key to lowercase in admin search qeury builder and in aggregation

## [2.24.0] - 2020-03-30
### Added
- VIMEET-2059 - Assign a conference to a webinar / Add error message when speaker is not a user
- VIMEET-2063 - Login security: disable temporarily for 15 mintes user account after 5 wrong attempts
- VIMEET-2056 - Delete ajax request count participants

## [2.23.0] - 2020-03-16
### Added
- VIMEET-2055 - New password security

## [2.22.0] - 2020-03-13
### Added
- VIMEET-1493 - Auto refresh agenda when it is event D day
### Fixed
- Hotfix: Error message when no template form data to export

## [2.21.0] - 2020-03-05
### Added
- VIMEET-2030 - View checkin status in contacts list and agenda
### Fixed
- VIMEET-2054 - Fix catalog nomenclature filters
- VIMEET-2011 - Hotfix: priorisation meeting request before planning
- MV-223 - Doesn't access to a removed account
- MV-219 - send activate account on fastCheckin
- Improvment: Add cache to Crisp chat component

## [2.20.0] - 2020-02-25
### Fixed
- MV-222 - Take account of plan included product that can enable participant scan

## [2.19.0] - 2019-02-24
### Added
- VIMEET-2039 - Sheet agenda

## [2.18.0] - 2020-02-21
### Added
- VIMEET-2011 - Prioritize meeting request before planning
- VIMEET-2047 - Add number max of meetings per sheet in participation type parameter
- VIMEET-2046 - Limit upload image to 500ko
- VIMEET-2049 - Fix nomenclature template object value: cast key to string
### Fixed
- MV-204 - Don't send email if avertisment of changing mail is disabled

## [2.17.0] - 2020-01-31
### Added
- VIMEET-2033 - add number max of happenings per user in participation type parameters
- VIMEET-2040 - Set 'contains' as default operator for string filter
- VIMEET-2042 - Clean nomenclature key, replace dot by underscore
- VIMEET-2041 - restriction registration when number max of happenings per user is achieved
### Fixed
- MV-202 - Remove type from mass unavailability
- MV-209 - Fix video test; check if chat element exists

## [2.16.0] - 2019-12-23
### Added
- VIMEET-2034 - Follow the registration path

## [2.15.0] - 2019-12-19
### Added
- VIMEET-2026 - Fixes for API Leni
- VIMEET-2031 - Access control can be enabled or disabled
- VIMEET-2028 - Configure a registration path

## [2.14.2] - 2019-11-26
### Fixed
- MV-208 - Do not close badge when printing

## [2.14.1] - 2019-11-19
### Fixed
- MV-205 - Fix contacts indicators

## [2.14.0] - 2019-11-18
### Added
- VIMEET-1996 - Export analytics about contact evaluations per user
- VIMEET-1998 - Dashboard with contacts indicators
- VIMEET-1997 - Dashboard with access control indicators

## [2.13.0] - 2019-10-09
### Added
- VIMEET-2021 - As admin or organizer, delete all meetings if agenda not published
### Fixed
- MV-195 - Print user planning in badge scan screen
- MV-195 - Badge participation type background color
- MV-201 - Show user contact information
- MV-200 - Badge header background color

## [2.12.2] - 2019-09-26
### Fixed
- MV-195 - Wait images are loaded before printing badge

## [2.12.1] - 2019-09-25
### Fixed
- MV-199 - Limit folder name when exporting uploaded files

## [2.12.0] - 2019-09-16
### Fixed
- MV-190 - Show always opened tip after two hours
- MV-89 - Do not take account of disabled type/category (zero result) in catalog filter
- MV-182 - Show amount included taxes when event does not have taxes
### Added
- VIMEET-1889 - Take account of type rules when there is no categories rules
- VIMEET-2019 - View multiple sheets contacts list; Impersonate as every user of a participation sheet

## [2.11.0] - 2019-08-27
### Added
- MV-187 - Upload vector image
- MV-189 - As an admin, generate a user reset password url
- MV-172 - Capitalize first character of firstname
- VIMEET-1935 - Generate promo codes
- VIMEET-2018 - Export promo codes group
- VIMEET-2017 - Update promo codes group
### Fixed
- MV-193 - Reindex sheets after sending emailing

## [2.10.0] - 2019-07-05
### Added
- VIMEET-1916 - Add new parameters to badge
- VIMEET-1982 - Contacts download and list
- VIMEET-1999 - As an admin, remove promotion code from order
- VIMEET-1961 - Update translations from admin UI and deploy process
- VIMEET-2001 - Add fast on-site register and checkin
- VIMEET-2009 - Google and Linkedin login enabling on one event
- VIMEET-1081 - Priority meeting request number by type
- VIMEET-1648 - Terms of sale links
- VIMEET-2004 - Stop notifications on pending planner job
- VIMEET-1082 - Prioritize meeting request
- VIMEET-2020 - Get uploaded sheets files from a public route
### Fixed
- MV-184 - Fix rooming list assign dates
- MV-185 - Relevance filter choice by default
- MV-186 - Show contacts list when event is opened
- MV-188 - Index Sheet zipcode when country not defined
- MV-191 - Do not block meeting slot in planner pre process for linked sheets

## [2.9.0] - 2019-06-12
### Added
- VIMEET-1990 - Administrate tip message conditions
- VIMEET-1991 - Conditionnal tip message
- VIMEET-1995 - add products in cart counter (rebuilt of events css needed)
- VIMEET-1940 - Save rooming list filters
- VIMEET-1992 - Package and contacts list tip message
- VIMEET-1250 - Sheet filter by emailing (reindexation of all events needed)

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
