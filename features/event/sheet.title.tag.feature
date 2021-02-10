@event
@sheet
Feature: Sheet with a tag in editable text title
  I see the organization name in the sheet title

  Scenario: I can see the organization name in the sheet title
    Given the database is purged
    And the event "ASD Days" is created
    And there is a type in this event

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet with the title "Aanera"
    And there is a participant for this sheet and this user

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "Titre de votre fiche"

    When I follow "sheet.object.action.edit \"Titre de votre fiche\""
    And I fill in "sheet_editable_text_data[content]" with "World company"
    And I press "sheet_editable_text_data_submit"
    Then I should see "World company"
