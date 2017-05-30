@admin @event @tips
Feature: List of tips by event and type
  I see the list of tips affected to event

  Scenario: See the list o tips
    Given the database is purged
    And the event "Meetup Elao" is created
    And the tip "Awesome tip" is created
    And I am logged as admin
    And I go to this page "/fr/tip/1/list"
    Then I should see "admin.tip.list.title"
    And I should see "Awesome tip"

