Feature: Complete my package
  I need to be able to buy plan, participants, planning and options

  Scenario: I can buy plan
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016Event.yml              |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I am on this page "/fr"
    And I go to this page "/fr/sheet"
    And I follow "package.title"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should see "Formule B2B Meeting"
    And I should see "Stand équipé 4m² (2x2m)"
    When I check radio "plans_plan_2"
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    When I go to this page "/fr/sheet/1/package/step/1"
    Then The radio "plans_plan_2" should be checked

  Scenario: I can buy planning
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I am on this page "/fr/sheet/1/package/step/2"
    Then I should see "Packs de rendez-vous"
    And the ".user__formule" element should contain "195"
    And the ".product-price" element should contain "package.product.price"
    When I fill in "participant_and_planning[planningQuantity]" with "1"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
