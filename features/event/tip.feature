@event @agenda @catalog

  Feature: Tip message on front
    As a participant I can see tip message when they are set

  Scenario: I can see tip message on meeting management
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Category.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Rule.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Tip.yml                           |
    And I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr"
    When I go to this page "/fr/sheet/1/meeting/request"
    Then I should see "tip_title"

  Scenario: I can see tip message on catalog
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Category.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Rule.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Tip.yml                           |
    And elastica is populate
    And I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr/catalog"
    Then I should see "tip_title"
