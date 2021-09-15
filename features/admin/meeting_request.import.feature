@admin
@meeting
Feature: See meeting request
  I can see meeting requests

  Background:
    Given the database is purged
    And the event "RdvCarnot" is created
    And there is a type "Fournisseur" in this event

    And the user "user_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user

    And the user "user_2@proximum.com" is created
    And there is a sheet for this type with the title "Acme"
    And there is a participant for this sheet and this user

    And I am logged as admin
    And I am on this page "/fr/event"

    When I follow "admin.meeting_request.link"
    Then I should be on this page "/fr/event/1/meeting-request"

    # Import button
    When I follow "admin.meeting_request.action.import"
    Then I should see "event.meeting.request.import.title"

  Scenario: I can import a meeting request defined in a csv file
    When I attach the file "MeetingRequestImport/valid-file.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "flash.admin.meeting_request.import.success"
    Then I should see "Aanera"
    Then I should see "Acme"

  Scenario: I can't import a file that is not csv
    # not-csv-file.csv is an image file, though it has a csv extension
    When I attach the file "MeetingRequestImport/not-csv-file.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "event.meeting.request.import.title"
    And I should see "validators.field.notValid.uploadObject"

  Scenario: I can't import a meeting request if file has not correct headers
    When I attach the file "MeetingRequestImport/incorrect-headers.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "event.meeting.request.import.title"
    And I should see "validators.authentication_token.csv.invalid_keys"

  Scenario: I can't import a meeting request if email unkown
    When I attach the file "MeetingRequestImport/unknown-email.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "event.meeting.request.import.title"
    And I should see "form.meeting_request_import.children.file.error.user_not_found"

  Scenario: I can't import a meeting request if user has no sheet
    Given the user "user_3@proximum.com" is created
    When I attach the file "MeetingRequestImport/no-participant.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "event.meeting.request.import.title"
    And I should see "form.meeting_request_import.children.file.error.participant_not_found"

  Scenario: I can't import a meeting request if 2 users are on same sheet
    When I attach the file "MeetingRequestImport/same-sheet.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "event.meeting.request.import.title"
    And I should see "form.meeting_request_import.children.file.error.users_of_same_sheet"

  Scenario: I can't import a meeting request if an email has invalid format
    When I attach the file "MeetingRequestImport/invalid-email.csv" to "import[file]"
    And I press "import[submit]"
    Then I should see "event.meeting.request.import.title"
    And I should see "form.meeting_request_import.children.file.error.invalid_email"
