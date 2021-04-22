@admin @rule

Feature: add rule who see who
  I need to be able to add a rule for categories and types

  Background: Database purged and admin creates a who see who rule
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

  Scenario: New rule is correctly created
    Then the "priority" field should contain "4"
    And I should see "Who.see_who_but_dont_see"
    And I should see "form.rule_see_what.children.seeWhat.label"
    Given I am on this page "/fr/event/1/who-see-who"
    Then I should see "admin.rule.list.content.category \"Visiteur\""
    And I should see "admin.rule.list.content.type \"Type 1\""

  Scenario: I can edit created rule
    When I select "sheet_title" from "form.rule_see_what.children.seeWhat.label"
    And I additionally select "participant_lastname" from "form.rule_see_what.children.seeWhat.label"
    And I additionally select "participant_firstname" from "form.rule_see_what.children.seeWhat.label"
    And I fill in the following:
      | rule_see_what_priority                 | 1 |
      | rule_see_what_phoneAccessMinEvaluation | 1 |
      | rule_see_what_emailAccessMinEvaluation | 2 |
      | rule_see_what_sendEmailMinEvaluation   | 3 |
      | rule_see_what_sendEmailMinEvaluation   | 3 |
    And I check "rule_see_what_requestAutomaticallyTransformedIntoMeeting"
    And I press "rule_see_what_submit"
    Then I should be on this page "/fr/event/1/who-see-who"
    And I should see "flash.admin.event.who_see_what.success"
    Given I am on this page "/fr/event/1/who-see-who/see-what/1"
    Then the "priority" field should contain "1"
    And "participant_lastname" should be selected in "rule_see_what_seeWhat" input
    And "participant_firstname" should be selected in "rule_see_what_seeWhat" input
    And the "rule_see_what_phoneAccessMinEvaluation" field should contain "1"
    And the "rule_see_what_emailAccessMinEvaluation" field should contain "2"
    And the "rule_see_what_sendEmailMinEvaluation" field should contain "3"
    And the "rule_see_what_sendEmailMinEvaluation" field should contain "3"
    And the checkbox "rule_see_what_requestAutomaticallyTransformedIntoMeeting" should be checked

  Scenario: I can delete a rule
    Given I am on this page "/fr/event/1/who-see-who"
    When I press "admin.rule.list.action.delete"
    Then I should be on this page "/fr/event/1/who-see-who"
    And I should not see "admin.rule.list.content.category \"Visiteur\""
    And I should not see "admin.rule.list.content.type \"Type 1\""
