@event @sheet @package @product @order
Feature: Select payable option in sheet

  Scenario: I can select "Petit logo" payable option for my image object
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And there is a sheet template
    And there is a type "Exposant" in this event

    And there is a package "Pack participants+rdv" for this event
    And this package is assigned to this type

    And there is an option called "Petit logo" with a price of "10"

    And there is a plan called "Formule Logos" with a price of "99"
    And this plan is assigned to this package
    And this plan includes these options

    And there is an option called "Grand logo" with a price of "20"
    And these options are added to logo in sheet template
    And these options are assigned to this package

    # package must have at least one product participant and one product planning
    And there is a product Participant called "Participant Supplémentaire" with a price of "130" and a max quantity of 10
    And this product participant is assigned to this package
    And there is a planning called "Planning meetings" with a price of "39"
    And this product planning is assigned to this package

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is billing info for this sheet
    And there is a participant for this sheet and this user

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"

    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"

    When I attach the file "dummy-image-test.jpg" to "sheet_image_data_file"
    And I select "0" from "sheet_image_data[selectedProduct]"
    And I press "sheet_image_data_submit"
    Then I should be on this page "/fr/sheet/1/fr"
    And I should not see "Ajouter un logo"

    When I follow "Ajouter un logo"
    And I should see "sheet.object.image.remove"
    And the radio "sheet_image_data_selectedProduct_1" should be checked

  Scenario: I can select "Grand logo" payable option for my media object
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I go to this page "/fr/sheet/1"
    # click on image to edit logo (actually does NOT add)
    And I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"

    # Select "Grand logo" option
    When I select "1" from "sheet_image_data[selectedProduct]"
    And I press "form.sheet_image_data.children.submit.label"
    Then I should be on this page "/fr/sheet/1/fr"

    When I follow "Ajouter un logo"
    Then the radio "sheet_image_data_selectedProduct_3" should be checked

  Scenario: I should have "Petit logo" and "Grand logo" in my package
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/1"
    And I select "0" from "plans[plan]"
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "package.select"

    When I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And the "options[1][quantity]" field should contain "1"
    And the "options[3][quantity]" field should contain "1"

  Scenario: I can't remove "Petit logo" or "Grand logo" from my package because their are payable option
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And there is an existing sheet with the title "Aanera" in this event
    And there is an existing plan called "Formule Logos"
    # Assume plan is paid
    And there is an order with the amount of 100 for this sheet
    And there is this plan for this order

    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/2"
    Then I fill in "options[1][quantity]" with "2"
    Then I fill in "options[3][quantity]" with "1"
    When I press "package.product.validate"
    And I go to this page "/fr/sheet/1/package/step/2"
    Then I fill in "options[1][quantity]" with "0"
    Then I fill in "options[3][quantity]" with "0"
    When I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "package.product.quantityMinPayableOption"

  Scenario: I can't see the options that are included in the plan when editing my sheet
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr/sheet/1"
    And I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should not see "Petit logo"
    And I should not see "Grand logo"

    When I press "form.sheet_image_data.children.submit.label"
    Then I should be on this page "/fr/sheet/1/fr"

  Scenario: I should see option included in plan
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I go to this page "/fr/sheet/1/package/step/2"
    Then I should see "package.options.included"

  Scenario: I can remove my payable option in image object
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr/sheet/1"

    When I follow "Ajouter un logo"
    Then the response status code should be 200
    When I press "sheet.object.image.remove"
    Then I should be on this page "/fr/sheet/1"
    And I should see "Ajouter un logo"
