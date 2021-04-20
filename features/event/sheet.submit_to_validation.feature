@event @sheet @mail
Feature: Sheet validation workflow
  I can send my sheet to validation when I think I'm done

  Scenario: I can send my sheet to validation
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And there is a type "Exposant" in this event

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And this sheet has "100" as completeness
    And there is a participant for this sheet and this user

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "sheet.submit.validation"

    When I follow "sheet.submit.validation"
    Then I should be on this page "/fr/sheet/1"
    And I should see "sheet.submit.validation.pending"
