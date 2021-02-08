@event @sheet @package @product @order
Feature: Select payable option in sheet

  Scenario: I can select "Option chaise" payable option for my image object
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"
    When I attach the file "dummy-image-test.jpg" to "sheet_image_data_file"
    And I check radio "sheet_image_data_selectedProduct_6"
    And I press "sheet_image_data_submit"
    Then I should be on this page "/fr/sheet/1/fr"
    And I should not see "Ajouter un logo"
    When I follow "Ajouter un logo"
    And I should see "sheet.object.image.remove"
    And the radio "sheet_image_data_selectedProduct_6" should be checked

  Scenario: I can select "Option E" payable option for my media object
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"
    And I check radio "sheet_media_collection_data_selectedProduct_11"
    When I press "form.sheet_media_collection_data.children.submit.label"
    Then I should be on this page "/fr/sheet/1/fr"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the radio "sheet_media_collection_data_selectedProduct_11" should be checked

  Scenario: I should have "Option Chaise" and "Option E" in my package
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/1"
    Then I check radio "plans_plan_1"
    When I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Participants & plannings"
    When I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And the "options[6][quantity]" field should contain "1"
    And the "options[11][quantity]" field should contain "1"

  Scenario: I can't remove "Option Chaise" or "Option E" from my package because their are payable option
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/3"
    Then I fill in "options[6][quantity]" with "0"
    When I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And I should see "package.product.quantityMinPayableOption"

  Scenario: I can't see the option "Option 4m² supplémentaires Fournisseur" that is included on the plan
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should not see "sheet_image_data_selectedProduct_5"
    And I press "form.sheet_image_data.children.submit.label"
    Then I should be on this page "/fr/sheet/1/fr"

  Scenario: I can't see the option "Option F" that is included on the plan
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the response status code should be 200
    And I should not see "sheet_media_collection_data_selectedProduct_12"
    When I press "form.sheet_media_collection_data.children.submit.label"
    Then I should be on this page "/fr/sheet/1/fr"

  Scenario: I should not see "Option 4m2" and "Option F" on the package because their are included
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/3"
    Then I should see "package.options.included"
    And the "options[5][quantity]" field should contain "0"
    And the "options[12][quantity]" field should contain "0"

  Scenario: I can remove my payable option in image object
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I press "sheet.object.image.remove"
    Then I should be on this page "/fr/sheet/1"
