@admin @rule

Feature: add rule who see who
  I need to be able to add a rule for categories and types

  Scenario: add rule
    Given the database is purged
    And the event "RDV Carnot 2020" is created
    And there is a type in this event
    And there is a participant category "Exposant" for this event
    And there is a participant category "Visiteur" for this event
    And I am logged as admin
    And I am on this page "/fr/event/1/who-see-who"
    When I fill in the following:
      | who_see_who_seer    | category:2 |
      | who_see_who_seeable | type:1     |
      | priority            | 4          |
    And I press "form.who_see_who.children.submit.label"
    Then I should be on this page "/fr/event/1/who-see-who/see-what/1"
    And the "priority" field should contain "4"
    And I should see "Who.see_who_but_dont_see"
    And I should see "form.rule_see_what.children.seeWhat.label"
    When I select "sheet_title" from "form.rule_see_what.children.seeWhat.label"
    And I additionally select "participant_lastname" from "form.rule_see_what.children.seeWhat.label"
    And I additionally select "participant_firstname" from "form.rule_see_what.children.seeWhat.label"
    And I press "form.rule_see_what.children.submit.label"
    Then I should see "flash.admin.event.who_see_what.success"
