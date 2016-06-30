@event
@package
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
    And I should see "sheet.object.action.add"
    # This needs to be redefined as it doesn't show the price anymore
    And the ".user__formule" element should contain "package.product.unitPrice"
    And the ".product-price" element should contain "package.product.unitPrice"
    When I fill in "participant_and_planning[planningQuantity]" with "1"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"

  Scenario: I can buy options
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I am on this page "/fr/sheet/1/package/step/3"
    Then I should see "Options d’exposition"
    And I should see "Options d'exposition et de communication"
    And I should see "Options de communication"
    When I fill in the following:
      | options_10 | 1  |
      | options_11 | 10 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And I should see "package.product.quantityNotMatch"
    When I fill in the following:
      | options_10 | 1 |
      | options_11 | 3 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet"
    When I go to this page "/fr/sheet/1/package/step/3"
    Then the "options_10" field should contain "1"
    Then the "options_11" field should contain "3"

    Scenario: I can add a participant at step 2
      Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
      When I am on this page "/fr/sheet/1/package/step/2"
      Then I should see "sheet.object.action.add"
      And I follow "sheet.object.action.add"
      And I should see "sheet.participant.sendInvite"
      And I fill in the following:
        | add_participant_firstName | Truc         |
        | add_participant_lastName  | Test         |
        | add_participant_email     | truc@test.fr |
      Then I press "sheet.participant.sendInvite"
      And I should be on this page "/fr/sheet/1/package/step/2"
      And I should see "Truc TEST"
      And I should see "TT"
