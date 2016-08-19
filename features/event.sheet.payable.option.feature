@event
@sheet
Feature: Select payable option in sheet

  Scenario: I can select "Option chaise" payable option for my image object
    Given the database is empty
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
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"
    When I attach the file "dummy-image-test.jpg" to "sheet_image_data_file"
    And I check radio "sheet_image_data_selectedProduct_6"
    And I press "sheet_image_data_submit"
    Then I should be on this page "/fr/sheet"
    And I should not see "Ajouter un logo"
    When I follow "object-image-edit"
    And I should see "sheet.object.image.remove"
    And The radio "sheet_image_data_selectedProduct_6" should be checked

  Scenario: I can select "Option E" payable option for my media object
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"
    And I check radio "sheet_media_collection_data_selectedProduct_9"
    When I press "form.sheet_media_collection_data.children.submit.label"
    Then I should be on this page "/fr/sheet"
    When I follow "sheet.object.action.edit \"Médias\""
    Then The radio "sheet_media_collection_data_selectedProduct_9" should be checked

  Scenario: I should have "Option Chaise" and "Option E" in my package
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I go to this page "/fr/sheet/1/package/step/1"
    Then I check radio "plans_plan_1"
    When I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Participants & plannings"
    When I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And the "options[6]" field should contain "1"
    And the "options[11]" field should contain "1"

  Scenario: I can't remove "Option Chaise" or "Option E" from my package because their are payable option
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I go to this page "/fr/sheet/1/package/step/3"
    Then I should see "package.product.selectOptions"
    And I fill in "options[6]" with "0"
    When I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And I should see "package.product.quantityMinPayableOption"

  Scenario: I can change my image payable option to "Option 4m² supplémentaires Fournisseur" that are included on the plan
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "object-image-edit"
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"
    And I check radio "sheet_image_data_selectedProduct_5"
    And I press "form.sheet_image_data.children.submit.label"
    Then I should be on this page "/fr/sheet"
    When I follow "object-image-edit"
    Then The radio "sheet_image_data_selectedProduct_5" should be checked

  Scenario: I can change my media payable option to "Option F" that are included on the plan
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the response status code should be 200
    And I check radio "sheet_media_collection_data_selectedProduct_12"
    When I press "form.sheet_media_collection_data.children.submit.label"
    Then I should be on this page "/fr/sheet"
    When I follow "sheet.object.action.edit \"Médias\""
    Then The radio "sheet_media_collection_data_selectedProduct_12" should be checked

  Scenario: I should not see "Option 4m2" and "Option F" on the package because their are included
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I go to this page "/fr/sheet/1/package/step/3"
    Then I should see "package.product.selectOptions"
    And I should see "package.options.included"
    And the "options[5]" field should contain "0"
    And the "options[12]" field should contain "0"

  Scenario: I can remove my payable option in image object
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I follow "object-image-edit"
    Then the response status code should be 200
    And I press "sheet.object.image.remove"
    Then I should be on this page "/fr/sheet"
    When I follow "Ajouter un logo"
    Then the radio "sheet_image_data_selectedProduct_5" should not be checked
    And the radio "sheet_image_data_selectedProduct_6" should not be checked
    And the radio "sheet_image_data_selectedProduct_7" should not be checked
