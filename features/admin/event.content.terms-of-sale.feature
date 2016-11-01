@admin
@admin-event
Feature: Update terms of sale
  I need to be able to update the terms of sale of my event

  Scenario: Update terms of sale
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    When I go to this page "/admin/en/event"
    Then I should see "Les rendez-vous CARNOT 2016"
    And I go to this page "/admin/en/event/1"
    When I follow "admin.update_content.terms-of-sale.link"
    And I should be on this page "/admin/en/event/1/content/terms-of-sale/update"
    And I fill in the following:
      | content_update_translations_fr_value | Bla Bla Bla  |
      | content_update_translations_en_value | Foo Bar Foo  |
    And I press "form.content_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.content.update_terms-of-sale.success"
    And I should be on this page "/admin/en/event/1/content/terms-of-sale/update"
