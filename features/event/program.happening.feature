@event @happening @program
Feature: Program Happening
  As a participant, I can see the program of happening of the event

  Scenario: I can see the program of happening of the event
    Given the database is purged
    And the event "ASD Days" is created
    And there is a type in this event
    And this event happens september 1 2020

    # user has a sheet
    And there is a sheet for this type with the title "Proximum"
    And the user "user_asddays_1@proximum.com" is created
    And there is a participant for this sheet and this user

    # there is a conference
    And the happenings are open
    And there is a webinar in this event
    And this type can access this happening

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "program.title"

    When I go to this page "/fr/sheet/1/program"
    Then I should be on this page "/fr/sheet/1/program"
    And I should see "Webinar"
    And I should see "Présentation flash"
