@event @navigation
Feature: Navigate in the application using menu

  Scenario: I can change my application language and stay where I am
    Given the database is purged
    And the event "Super event" is created
    And there is a type in this event
    And there is a rule for this type and this event
    And there is a sheet for this type with the title "Proximum"
    And the user "user_asddays_1@proximum.com" is created
    And there is a participant for this sheet and this user
    When I am logged with "user_asddays_1@proximum.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I follow "en"
    Then I should be on this page "/en/sheet/1"
    When I follow "navigation.links.member_space.profile"
    Then I should be on this page "/en/account/sheet/1/participant/1"
    When I follow "fr"
    Then I should be on this page "/fr/account/sheet/1/participant/1"

