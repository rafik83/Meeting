@admin @event @tips
Feature: List of tips by event and type
  I see the list of tips affected to event

  Scenario: See the list of tips for an event
    Given the database is purged
    And the tip "Awesome tip" is created for the event "Best of Web"
    And I am logged as admin
    And I go to this page "/fr/event/1/tip/list"
    Then I should see "admin.tip.list.title"
    And I should see "Awesome tip"

  Scenario: I can affect a tip to an event
    Given the database is purged
    And the event "Meetup Elao" is created
    And the tip "Awesome tip" is created
    And I am logged as admin
    And I am on this page "/fr/event/1/tip/list"
    Then I should see "admin.tip.event.affect.link"
    When I follow "admin.tip.event.affect.link"
    Then I should be on this page "/fr/event/1/tip/affect"
    And I should see "admin.tip.event.affect.form.title"
    When I select "Awesome tip" from "tip_event_affect[tip]"
    And I press "tip_event_affect_submit"
    Then I should be on this page "/fr/event/1/tip/list"
    And I should see "flash.admin.tip.affect.success"

  Scenario: I can remove a tip from an event
    Given I am logged as admin
    And the tip "Awesome tip" is created for the event "Best of Web"
    And I am on this page "/fr/event/2/tip/list"
    When I press "admin.tip.event.remove.link"
    Then I should be on this page "/fr/event/2/tip/list"
    And I should see "flash.admin.tip.remove.success"
    And I should not see "Awesome tip"

  Scenario: I can see preview of a tip when I affect to an event
    Given the database is purged
    And I am logged as admin
    And the tip "Le saviez vous?" is created
    When I send a "GET" request to "/fr/tip/1/preview/fr"
    Then the response should be in JSON
    And the JSON should be equal to:
      """
      {
          "title": "Le saviez vous? (fr)",
          "content": "content_fr",
          "pages": [
              "admin.tip.column.visible.catalog",
              "admin.tip.column.visible.meeting_management",
              "admin.tip.column.visible.print_planning",
              "admin.tip.column.visible.onSheet",
              "admin.tip.column.visible.onAgenda",
              "admin.tip.column.visible.onPackage",
              "admin.tip.column.visible.onContacts",
              "admin.tip.column.visible.onProgram",
              "admin.tip.column.visible.onConfirmationPhone",
              "admin.tip.column.visible.onNetworking"
          ]
      }
      """
