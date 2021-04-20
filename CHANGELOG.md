# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- Fix duplicated jobs

## [2.87.3] - 2021-04-20
### Fixed
- Fix sheetToDisplayId route parameter
- Fix exceptions when country code is empty or unsupported

## [2.87.2] - 2021-04-20
### Fixed
- Fix permission service in meeting request controller
- Failover if country code is not defined (in IntlAdapter)

## [2.87.1] - 2021-04-20
### Fixed
- Don't throw exception if country not defined

## [2.87.0] - 2021-04-20
### Updated
- VIMEET-2301 - Upgrade Symfony version to 4.4

### Fixed
- Display original URL on sheet in PDF format instead of redirect link

### Added
- VIMEET-2358 - Export meeting evaluations
- MV-275 - Open visio meeting from sheet not in meeting, in a context of multi-sheet user

## [2.86.2] - 2021-04-02
### Fixed
- MV-322 - webinar viewer's count has to be reset on WebRTC init
- MV-323 - Webinar note redirect fix

## [2.86.1] - 2021-03-31
### Fixed
- MV-321 - webinar vote form not displayed

## [2.86.0] - 2021-03-31
### Added
- VIMEET-2439 - Export conferences participants (phone / grade / connection date)
- VIMEET-2438 - Export conferences (Participant connected, Number of grades, Average grade)

## [2.85.1] - 2021-03-30
### Fixed
- MV-320  - check if possible slots are available

## [2.85.0] - 2021-03-29
### Added
- VIMEET-2358 - Export meeting evaluations

## [2.84.3] - 2021-03-29
### Fixed
- Don't return documents in ES aggregations, to optimize search engine usage

## [2.84.2] - 2021-03-22
### Fixed
- Don't update question count when a vote is notified
- MV-317 - Fix live url iframe displayed twice in webinar

## [2.84.1] - 2021-03-19
### Fixed
- MV-316 - Fix to activate your camera and microphone in webinar access

## [2.84.0] - 2021-03-19
### Added
- VIMEET-2436 - As admin I can check 'Participants must evaluate happening'
- VIMEET-2437 - As participant 'I must evaluate happening'
- VIMEET-2289 - Can change participants & slot directly from admin meeting edition
- VIMEET-2399 - Import meeting requests from CSV file
- VIMEET-2378 - Order conferences by date in admin panel

### Fixed
- MV-313 - Trunck happening name if too long for tokbox
- Code style / namespace fixes
- MV-314 - Fix unclickable stop sharing button

### Deprecated
- Remove "scanned" field from "contact" table

## [2.83.0] - 2021-03-15
### Security
- VIMEET-2373 - fix malicious files upload
- VIMEET-2373 - XSS fixes
- VIMEET-2373 - various IDOR fixes
- VIMEET-2373 - various broken access fixes

### Fixed
- Partners are not allowed to see users (see VIMEET-2373 comments)
- hotfix - limit chat messages and static avatar to avoid overload on webinars with 1000+ viewers

### Updated
- VIMEET-2373 - make admin's enumeration not possible (V02 intrusion test)

## [2.82.3] - 2021-03-05
### Fixed
- MV-311 - fix max size upload video 300M

## [2.82.2] - 2021-03-02
### Fixed
- MV-310 - fix error if desktop notifications are not available (ie on iOS)

## [2.82.1] - 2021-02-18
### Fixed
- MV-308 - fix "values doesn't support values of type: START_OBJECT" ES error

## [2.82.0] - 2021-02-16
### Updated
- Update Elasticsearch to 6.8.13

## [2.81.0] - 2021-02-16
### Added
- VIMEET-2361 - Add link to prefill user mail on registration

### Removed
- VIMEET-1811 - Remove can_move_meeting column from db

## [2.80.0] - 2021-02-15
### Added
- VIMEET-2359 - Micro and camera crossed out
- VIMEET-2189 - Mute speakers
- VIMEET-2386 - Add company name to api conference call

## [2.79.1] - 2021-02-05
### Fixed
- Redis query cache namespace

## [2.79.0] - 2021-02-04
### Added
- VIMEET-1811 - Participants can edit participants list and date of a meeting
- VIMEET-2319 - Send webinar record to speakers
- VIMEET-2323 - Link target blank
- VIMEET-2296 - Add participant locale in participants export
- VIMEET-2295 - Can import participant's locale

### Fixed
- VIMEET-2322 - Set events to public and transparent to enabled title display on Google calendar (else only busy is shown)

## [2.78.3] - 2021-02-03
### Fixed
- MV-306 - Don't show error when updating availabilities if a user unavailability conflicts with existing unavailability

## [2.78.2] - 2021-02-03
### Updated
- MV-306 - Add log when exception is thrown when saving availabilities

## [2.78.1] - 2021-01-21
### Fixed
- MV-300 - Can't close chat during visio meeting (BC break with chat lib update)

## [2.78.0] - 2021-01-20
### Fixed
- MV-295 - Badge preview from back-office

### Added
- VIMEET-2305 - participant agenda available by an ical file (only for admins)
- VIMEET-2306 - Add checkbox object for registration template
- VIMEET-2307 - Better visibility text
- VIMEET-2304 - Picto edit

### Updated
- VIMEET-2303 - Apply TVA if event country is fr and billing info is mc and Do not appy european vat for gb
- VIMEET-2293 - Don't ask participant's locale for one-locale events

## [2.77.0] - 2021-01-18
### Added
- VIMEET-2293 - Set participant's locale by event

## [2.76.0] - 2021-01-12
### Updated
- VIMEET-2287 - Participants can join webinar during running hours, even if they're not available

### Added
- VIMEET-2292 - Display speaker camera in PiP mode when sharing screen
- VIMEET-2191 - Reply to webinar question
- VIMEET-1811 - User can change participants to meeting

### Fixed
- MV-293 - Https links in emails

## [2.75.0] - 2020-12-24
### Updated
- Upgrade NODE version 14.x (LTS)

### Fixed
- MV-289 - Error management chat
- MV-291 - Exception simultaneous openings meetings

### Added
- VIMEET-2284 - Delete webinar question
- VIMEET-2184 - Speakers can prepare before their webinar
- VIMEET-2234 - API endpoint to access list of happenings
- VIMEET-2278 - Participant's program displayed according his timezone
- VIMEET-2253 - Increment unread message counters synchronously
- VIMEET-2275 - Close notification private chat
- VIMEET-2284 - Delete question webinar
- Add health check route

### Fixed
- MV289 - Error management chat

## [2.74.1] - 2020-12-14
### Fixed
- MV-292 - Restrict sendgrid http client to version 3.7

## [2.74.0] - 2020-12-12
### Updated
- Upgrade PHP to 7.4

## [2.73.0] - 2020-12-10
### Fixed
- fix overlay problem with opentok
- MV-284 - Fix race conditions on sessionId creation for call visio
- MV-284 - Generate tokbox token on each meeting start
- MV-290 - Internet Explorer doesn't support forEach method on NodeList

### Updated
- VIMEET-2121 - Improve visio settings UI, fix css for video-helper container

## [2.72.2] - 2020-12-07
### Fixed
- MV-285 - Custom button display on mobile

### Added
- VIMEET-2252 - Reactivate account
- VIMEET-2285 - Delete message chat webinar
- VIMEET-2249 - Notification page redirection

### Added
- VIMEET-2261 - Add pending request counter

## [2.72.1] - 2020-12-02
### Fixed
- MV-286 - Fix disconnection when alert is not closed

## [2.72.0] - 2020-12-02
### Added
- VIMEET-2185 - Add waiting image/video to webinar
- VIMEET-2248 - Help tip networking

### Updated
- VIMEET-2121 - Change visio test process, fix regressions due to changes in VideoConference

## [2.71.4] - 2020-11-30
### Fixed
- MV-281 - Fix webinar recording layout when HLS is activated

## [2.71.3] - 2020-11-25
### Fixed
- MV-282 - Delete header on player video SQY78

## [2.71.2] - 2020-11-23
### Fixed
- MV-278 - Video player SQY78

## [2.71.1] - 2020-11-22
### Fixed
- MV-279 - Fix video webinar

## [2.71.0] - 2020-11-20
### Added
- VIMEET-2255 - Add speaker's notification desktop webinar: I present
- VIMEET-2247 - Limit height of spkeaker's title in webinars

### Fixed
- MV-268 - Start higher button webinar
- MV-277 - Allow to go to my agenda from the sheet agenda

## [2.70.1] - 2020-11-19
### Revert
- MV-275 - Open visio meeting from sheet not in meeting, in a context of multi-sheet user

## [2.70.0] - 2020-11-18
### Added
- VIMEET-2269 - Call visio analytics in participants export

### Fixed
- MV-273 - Fix count in multi-sheet meeting requests list
- MV-275 - Open visio meeting from sheet not in meeting, in a context of multi-sheet user
- VIMEET-2198 - Show viewers and timer for viewers in hls mode

## [2.69.1] - 2020-11-16
### Added
- VIMEET-2137 - Allow user to access someone else agenda

## [2.69.0] - 2020-11-16
### Added
- VIMEET-2270 - Contact tab display if call visio
- VIMEET-2268 - Call visio analytics on admin dashboard
- VIMEET-2220 Compute the private chat message count
- VIMEET-2232 - Allow user to access video webinar when ended even with no participation

## [2.68.0] - 2020-11-09
### Added
- VIMEET-2198 - Broadcast webinars in HLS

## [2.67.1] - 2020-11-09
### Fixed
- MV-270 - Impossible to open a contact after a meeting

## [2.67.0] - 2020-11-09
### Fixed
- VIMEET-2223 - ClearTimeout missing.

### Added
- VIMEET-2186 - Notifications when new messages or questions during webinar.

### Changed
- MV-267 - Brazil abolished DST in 2019

## [2.66.1] - 2020-11-05
### Fixed
- MV264 - Networking tab only validated or accepted

## [2.66.0] - 2020-11-04
### Added
- VIMEET-2223 - Add call visio on private chat (tab networking).
- VIMEET-2229 - Add field date call visio in Admin.
- VIMEET-2260 - Add button custom 2.

## [2.65.2] - 2020-11-02
### Fixed
- Hotfix - Add default values for analytics options on participant type, to avoid error when type is created by an organizer admin

## [2.65.1] - 2020-10-29
### Fixed
- Disable session lock on redis to avoid timeouts for some sessions

## [2.65.0] - 2020-10-29
### Added
- VIMEET-2225 - Add related product to video template objects

## [2.64.0] - 2020-10-28
### Fixed
- MV-262 - Fix issue in campaign target selector, when using hasRemainingToPay filter

### Updated
- VIMEET-2208 - Add counts to visit filters in catalog

## [2.63.0] - 2020-10-28
### Updated
- VIMEET-2218 - Check networking access to avoid sending useless notifications
- VIMEET-2242 - Suite Chat Vimeet - Button separate.
- update translations

## [2.62.1] - 2020-10-26
### Fixed
- disable redis doctrine cache

## [2.62.0] - 2020-10-26
### Added
- VIMEET-2208 - Filter meeting request by sheet viewed

### Fixed
- VIMEET-2219 - Apply filter on users added on the fly
- Add sheet card tag for datetime
- Fix impersonation route for role with ROLE_ALLOWED_TO_SWITCH
- VIMEET-2229 - Add call visio networking tab by key date

## [2.61.0] - 2020-10-15
### Added
- VIMEET-2244 - Tech Event - Api mapping configuration is now conditional by type.

## [2.60.0] - 2020-10-14
### Added
- VIMEET-2240 - Tech Event - custom button in menu use identifier in md5.
- VIMEET-2245 - Tech Event - retrieve identifier in md5 from source.

### Fixed
- VIMEET-2224 - Avoid exceptions related to chat and networking submenu

## [2.59.1] - 2020-10-13
### Updated
- Update translations

## [2.59.0] - 2020-10-13
### Added
- VIMEET-2245 - Login with tech event token in md5

## [2.58.0] - 2020-10-13
### Added
- VIMEET-2236 - Add participant id for custom button
- VIMEET-2240 - Add tech event id contact in md5 for custom button
- VIMEET-2240 - Add sheet id for custom button
- VIMEET 2218 - Add tab private chat and tab general chat

### Fixed
- MV255 - move chat css to event guideline css
- MV255 - move question css to event guideline css
- MV255 - separate button chat and button question

## [2.57.0] - 2020-10-13
### Added
- VIMEET-2218 - Networking tab and page
- VIMEET-2219 - Networking tab search participant
- VIMEET-2222 - Networking / Private chat
- VIMEET-2221 - Private chat notification toast
- VIMEET-2220 - Badge on networking tab with unread messages

### Changed
- Remove command bus usage for translations update commands to prevent DB connections

### Revert
- VIMEET-1925 - Restore mercure version, with fixed issue on chat update

## [2.56.0] - 2020-10-09
### Added
- VIMET-2177 - Add metrics to sheet export

## [2.55.0] - 2020-10-08
### Fixed
- Hotfix - Prevent invalid extensions (especially php) on uploaded files

## [2.54.0] - 2020-10-07
### Fixed
- MV-254 - Display bullet point in help message
- Hotfix - Force nomenclature values to lower case (issue Franchise expo 2020)

## [2.53.0] - 2020-10-05
### Added
- VIMEET-2237 - Option to record webinars automatically

## [2.52.0] - 2020-10-01
### Revert
- VIMEET-1925 - Revert chat and questions with Mercure, reintroduce chat and questions from Tokbox

## [2.51.2] - 2020-10-01
### Added
- Hotfix - force refresh chat and questions in webinars

## [2.51.1] - 2020-10-01
### Added
- Hotfix - add message to force speakers to use chrome

## [2.51.0] - 2020-09-30
### Added
- VIMEET-1925 - New version of chat and questions decoupled from Tokbox

## [2.50.6] - 2020-09-29
### Fixed
- Use absolute urls in sheet for pdf export
- Use event url generator for absolute link on sheet pdf

## [2.50.5] - 2020-09-29
### Fixed
- Limit resolution for webinar screen sharing

## [2.50.4] - 2020-09-28
### Added
- Disable right click on webinar video for LM

## [2.50.3] - 2020-09-28
### Fixed
- Add _locale parameters in links to avoid exception when pdf is generated with cli

## [2.50.2] - 2020-09-28
### Fixed
- Don't show website url if value is empty to avoid exception on redirect page

## [2.50.1] - 2020-09-28
### Fixed
- Avoid exception by allowing null for some return types and arguments

## [2.50.0] - 2020-09-28
### Added
- VIMEET-2206 - Add option to activate analytics depending on participation type
- VIMEET-2205 - Display analytics on sheet
- VIMEET-2207 - Add analytics and number of meeting requests in participants export

### Fixed
- Prevent alert message to be displayed on a meeting approval when slots are available, even before dday (side effect of VIMEET-2203)

## [2.49.1] - 2020-09-25
### Fixed
- Prevent alert message to be displayed on a meeting approval when slots are available (side effect of VIMEET-2203)

## [2.49.0] - 2020-09-25
### Added
- VIMEET-2228 - Button custom menu

## [2.48.3] - 2020-09-24
### Fixed
- Allow fullscreen in iframe (js)

## [2.48.2] - 2020-09-24
### Fixed
- Allow fullscreen in iframe (webinars)

## [2.48.1] - 2020-09-24
### Added
- VIMEET-2231 - Redirect to program or agenda after webinar

### Updated
- Exclude from Sentry alert AccessDeniedHttpException

## [2.48.0] - 2020-09-24
### Added
- VIMEET-2217 - Activate / deactivate the networking tab by key date
- VIMEET-2204 - Hidden contact menu
- VIMEET-2203 - Request Automatically Transformed Into Meeting
- VIMEET-2233 - Disable right click on webinar video

## [2.47.0] - 2020-09-11
### Added
- VIMEET-2226 - Export multi-upload and media with sheet, add asynchronous export
- VIMEET-2156 - Remove explicit error about email on login (security)

## [2.46.0] - 2020-09-10
### Added
- VIMEET-2169 - Live streaming myevent

### Fixed
- Extra Data for token can be nullable on TechEventTokenAuthenticator

## [2.45.0] - 2020-09-09
### Added
- VIMEET-2175 - TechEvent Token Authenticator
- VIMEET-2161 - Record webinar
- VIMEET-2163 - Download webinar records for admin
- VIMEET-2164 - Change layout with screenshare on record
- VIMEET-2162 - Download webinar records for speaker
- Adapt command to a route, to count media/upload element on sheets

### Fixed
- No sheet result on campaign page without filter
- Hotfix: Fix datetime modification in AbstractTokenGenerator

## [2.44.0] - 2020-09-03
### Added
- VIMEET-1925 - Chat API
- VIMEET-2106 - The speaking user video is maximized based on audio level
- Deploy: DB migration without interaction
- VIMEET-2213 - Translate timezones
- VIMEET 2178 - In a webinar, participant can vote for a question
- Command to count media/upload element on sheets
- VIMEET-2175 - TechEvent api changed to handle login data, add guard to check login data on login

### Fixed
- Tip content can be nullable, and tip translations affected on event come from the event locales.

## [2.43.0] - 2020-08-27
### Added
- VIMEET-2187 - Invisible mode for webinar speaker
- VIMEET-2174 - Do not ask a new password when user is logged with a token

## [2.42.0] - 2020-07-31
### Added
- VIMEET-2168 - Video object on sheet template
- Add fly system and google cloud storage adapter
- VIMEET-2173 - Add option submit validation sheet
- Add fly system and google cloud storage adapter
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
- VIMEET-2161 - Record webinar

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
