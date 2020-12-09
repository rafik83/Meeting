@event
@sheet
Feature: Upload and remove image
  As a participant, I need to be able to upload and remove the image of my sheet

  Scenario: I can upload my image on the sheet
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
    When I attach the file "dummy-image-test.jpg" to "sheet_image_data_file"
    And I check radio "sheet_image_data_selectedProduct_6"
    And I press "sheet_image_data_submit"
    Then I should be on this page "/fr/sheet/1/fr"
    And I should not see "Ajouter un logo"

  Scenario: I can remove my uploaded image on the sheet
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    And I should not see "Ajouter un logo"
    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I press "sheet.object.image.remove"
    Then I should be on this page "/fr/sheet/1"
    And I should see "Ajouter un logo"
