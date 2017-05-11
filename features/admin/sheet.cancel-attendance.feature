@admin @attendance @sheet
Feature: Sheet attend the event
  I need to update the attendance status of a sheet

  Scenario: I can cancel the attendance of a sheet to the event
    Given the database is purged
    And the event "Best of web" is created
    And I am logged as admin
    And there is a sheet
    When I go to this page "/fr/event/1/sheet/1"
    And I should see "event.sheet.attend.present"
    Then I follow "event.sheet.attend.present"
    And I should be on this page "/fr/event/1/sheet/1/attend/form"
    And I check the "form.sheet_attendance_choice.children.attend.choice.cancel" radio
    And I press "form.sheet_attendance_choice.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "event.sheet.attend.cancel"
