@admin
@admin-event
@admin-happening
Feature: See, create and update happening
  I need to be able to see, create and update an happening

  Scenario: I can create a happening
    Given the database is purged
    And the event "La palourde en fête" is created
    And there is an happening category called "Cocktail" for this event
    And I am logged as admin
    When I go to this page "/fr/event/1/happening"
    And I follow "admin.happening.add"
    And I should be on this page "/fr/event/1/happening/create"
    When I fill in the following:
      | happening_create[category]                | 1                |
      | happening_create[translations][fr][title] | Degustation      |
      | happening_create[translations][en][title] | Tasting          |
      | happening_create[begin]                   | 09/09/2016 10:10 |
      | happening_create[end]                     | 09/09/2016 12:10 |
      | happening_create[questionAllowed]         | 1                |
      | happening_create[limitParticipant]        |                  |
    And I press "form.happening_create.children.submit.label"
    Then I should see "flash.admin.happening.create.success"
    And I should see "Degustation"

  Scenario: I can update a happening
    Given I am logged as admin
    And I am on this page "/fr/event/1/happening/1/update"
    When I fill in the following:
      | happening_update[category]                | 1                       |
      | happening_update[translations][fr][title] | Degustation de palourde |
      | happening_update[translations][en][title] | Clam Tasting            |
      | happening_update[questionAllowed]         | 0                       |
      | happening_update[limitParticipant]        | 100                     |
    And I press "form.happening_update.children.submit.label"
    And I should be on this page "/fr/event/1/happening/1/update"
    Then I should see "flash.admin.happening.update.success"
    And the "happening_update[translations][fr][title]" field should contain "Degustation de palourde"
    And the "happening_update[limitParticipant]" field should contain "100"

  Scenario: I can see a list of happening
    Given I am logged as admin
    When I go to this page "/fr/event/1/happening"
    Then I should see "admin.happening.title"
    And I should see "Degustation de palourde"

  Scenario: I can export happening participant
    Given I am logged as admin
    And I go to this page "/fr/event/1/happening"
    When I press "admin.happening.participations.button.export"
    Then I should be on this page "/fr/event/1/happening"
    And I should see "flash.admin.happening.participation.empty"

  Scenario: I can export happenings in french
    Given I am logged as admin
    When I go to this page "/fr/event/1/happening"
    Then I should see "admin.happening.button.export"
    Then I should see "Français" in the ".export_localize" element
    When I follow "Français" in the ".export_localize" element
    Then I should be on this page "/fr/event/1/happening/export/fr"

  Scenario: I can export happenings in english
    Given I am logged as admin
    When I go to this page "/fr/event/1/happening"
    Then I should see "admin.happening.button.export"
    Then I should see "Anglais" in the ".export_localize" element
    When I follow "Anglais" in the ".export_localize" element
    Then I should be on this page "/fr/event/1/happening/export/en"
