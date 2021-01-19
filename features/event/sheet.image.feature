@event
@sheet
Feature: Upload and remove image
  As a participant, I need to be able to upload and remove the image of my sheet

  Scenario: I can upload my image on the sheet
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And there is a product Participant called "Logo" with a price of "420" and a max quantity of 42
    And there is an attributable option called "Avec lien" with a price of "8"
    And there is an attributable option called "Sans lien" with a price of "4"
    And the user "user_asddays_1@proximum.com" is created
    And there is a type "Fournisseur" in this event

    And there is a package "Wonderful Package" for this event
    And this package is assigned to this type
    And there is a plan called "Formule premium" with a price of "99"
    And this plan is assigned to this package
    And these options are assigned to this package

    And child "1b9a00b3" of sheet templates has product options
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr/sheet/1"
    And I follow "Ajouter un logo"
    Then the response status code should be 200

    When I attach the file "dummy-image-test.jpg" to "sheet_image_data_file"
    #todo: replace sheet_image_data_selectedProduct_2 by another selector
    And I check radio "sheet_image_data_selectedProduct_2"
    And I press "sheet_image_data_submit"
    Then I should be on this page "/fr/sheet/1/fr"
    And I should not see "Ajouter un logo"

  Scenario: I can remove my uploaded image on the sheet
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr/sheet/1"
    And I should not see "Ajouter un logo"
    And I follow "Ajouter un logo"

    Then the response status code should be 200
    And I press "sheet.object.image.remove"
    Then I should be on this page "/fr/sheet/1"
    And I should see "Ajouter un logo"
