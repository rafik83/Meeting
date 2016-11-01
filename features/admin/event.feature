@admin
@admin-event
Feature: See, create and update event
  I need to be able to see, create and update an event

  Scenario: See event
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    When I go to this page "/admin/en/event"
    Then I should see "Les rendez-vous CARNOT 2016"

  Scenario: create event
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/en/event"
    And I should see "admin.event.create.title"
    Then I follow "admin.event.create.title"
    Then the response status code should be 200
    And I should be on this page "/admin/en/event/create"
    And I fill in the following:
      | form.event_create.children.title.label           | Super Event                     |
      | form.event_create.children.domain.label          | super-event.vimeet.proximum.dev |
      | form.event_create.children.vat.label             | 20                              |
      | form.event_create.children.leftColor.label       | #123456                         |
      | form.event_create.children.rightColor.label      | #123456                         |
      | form.event_create.children.textColor.label       | #123456                         |
      | form.event_create.children.organiserName.label   | Proximum                        |
    And I select "Europe/Paris" from "form.event_create.children.timeZone.label"
    And I select "fr" from "form.event_create.children.fallback.label"
    And I select "fr" from "form.event_create.children.locales.label"
    And I check the "form.vatMode.ati" radio
    And I select "FR" from "form.event_create.children.country.label"
    And I select "EUR" from "form.event_create.children.currency.label"
    And I press "form.event_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.create.success"
    And I should be on this page "/admin/en/event"
    And I should see "Super Event"

  Scenario: update event
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/en/event/1"
    When I follow "admin.event.update.link"
    Then the response status code should be 200
    And I should be on this page "/admin/en/event/1/update"
    And I fill in the following:
      | event_update_title                       | Other event                                                                    |
      | event_update_translations_fr_description | LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISE                                  |
      | event_update_translations_en_description | In 7 editions, les Rendez-vous CARNOT became the major R&D event for innotion. |
    And I select "fr" from "event_update_fallback"
    And I select "EUR" from "event_update_currency"
    And I press "form.event_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.update.success"
    And I go to "/admin/fr/event"
    And I should see "Other event"
    And I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr"
    Then the response status code should be 200
    And I should see "LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISE"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/en"
    And I should see "In 7 editions, les Rendez-vous CARNOT became the major R&D event for innotion."
