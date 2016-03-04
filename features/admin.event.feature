Feature: See and update event
  I need to be able to see and update an event

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |
      | app/Category.yml |
      | Admin.yml        |
    Given I am logged with "test@test.com" on admin
    And I go to "/admin/event"

  Scenario: see event
    Then I should see "Les rendez-vous CARNOT 2016"

  Scenario: update event
    When I follow "Modifier"
    Then the response status code should be 200
    And I should be on "/admin/event/1/update"
    And I fill in the following:
      | event_update_title                       | Other event                                                                    |
      | event_update_translations_fr_description | LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISE                                  |
      | event_update_translations_en_description | In 7 editions, les Rendez-vous CARNOT became the major R&D event for innotion. |
    And I select "en" from "event_update_fallback"
    And I select "fr" from "event_update_fallback"
    And I press "form.event_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.update.success"
    And I go to "/admin/event"
    And I should see "Other event"
    And I follow "Modifier"
    Then the response status code should be 200
    And I should see "LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISE"
    And I should see "In 7 editions, les Rendez-vous CARNOT became the major R&D event for innotion."
