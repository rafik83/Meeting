Feature: Handle Package
  I need to be able to create and list packages of an event

  Scenario: I can create a package linked to an event
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | Admins.yml                                                               |
    And I am logged with "test2@test.com" on admin
    Given I go to this page "/admin/fr/event"
    Then I follow "admin.package.link"
    And I should be on this page "/admin/fr/event/1/package"
    And I should see "admin.zero-result"
    Then I follow "admin.package_create.title"
    And I should be on this page "/admin/fr/event/1/package/create"
    And I fill in the following:
      | form.package_create.children.name.label                | PackageTitre |
      | package_create_translations_fr_title                   | Titre fr     |
      | package_create_translations_en_title                   | Titre en     |
      | package_create_translations_fr_descriptionTitle        | DescTitre fr |
      | package_create_translations_en_descriptionTitle        | DescTitre en |
      | form.package_create.children.unitPrice.label           | 20           |
      | form.package_create.children.participantIncluded.label | 2            |
    And I press "form.package_create.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/package"
    And I should see "admin.package.create.success"

  Scenario: I see the list of packages of an event
    Given I am logged with "test2@test.com" on admin
    And I go to this page "/admin/fr/event/1/package"
    Then I should see "PackageTitre"
