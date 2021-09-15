@event @sheet @package @product @order
Feature: Select payable option in sheet

  Scenario: I can pay my package with payable option "Grand logo"
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And there is a sheet template
    And there is a type "Exposant" in this event

    And there is a package "Pack participants+rdv" for this event
    And this package is assigned to this type

    And there is an option called "Petit logo" with a price of "10"

    And there is a plan called "Formule Petit logo" with a price of "99"
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
    And there is a participant for this sheet and this user

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"

    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"

    When I attach the file "dummy-image-test.jpg" to "sheet_image_data_file"
    And I select "1" from "sheet_image_data[selectedProduct]"
    And I press "sheet_image_data_submit"
    Then I should be on this page "/fr/sheet/1/fr"

    When I go to this page "/fr/sheet/1/package/step/1"
    And I select "0" from "plans[plan]"
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Planning meetings"
    When I press "package.participant_planning.validate"
    And I should be on this page "/fr/sheet/1/package/step/3"
    And the "options[3][quantity]" field should contain "1"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    And I check the "gender.man" radio
    And I fill in the following:
    | form.billing_info_update.children.lastname.label  | Jean         |
    | form.billing_info_update.children.firstname.label | Test         |
    | form.billing_info_update.children.function.label  | DG           |
    | form.billing_info_update.children.phone.label     | +33456789    |
    | form.billing_info_update.children.mobile.label    | +33456789    |
    | form.billing_info_update.children.email.label     | jean@test.fr |
    | form.billing_info_update.children.company.label   | ELAO-TEST    |
    | form.billing_info_update.children.street.label    | 10 Rue test  |
    | form.billing_info_update.children.zipcode.label   | 75002        |
    | form.billing_info_update.children.city.label      | PARIS        |
    | form.billing_info_update.children.country.label   | FR           |
    | form.billing_info_update.children.vatNumber.label | 123456789    |
    And I press "form.billing_info_update.children.submit.label"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "package.summary.pay"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    When I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    And I check the "form.payment_choice.children.paymentMode.bank_check" radio
    When I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"

  Scenario: I can't edit my image object payable option after order paid
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I go to this page "/fr/sheet/1"
    And I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should not see "sheet.object.option.buyable.label"
    And I should not see "Grand logo"

  Scenario: I can see my new payable option "Grand logo" in my package
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I go to this page "/fr/sheet/1"
    When I follow "navigation.links.package.order_summary_total"
    Then I should be on this page "/fr/sheet/1/order/summary"
    When I follow "package.summary.edit"
    Then I go to this page "/fr/sheet/1/package/step/2"
    And the "options[3][quantity]" field should contain "1"

  Scenario: I can pay my new payable option "Grand logo"
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And there is an existing sheet with the title "Aanera" in this event
    And there is an existing plan called "Formule Petit logo"
    And there is an order with the amount of 100 for this sheet
    And there is this plan for this order
    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/2"
    Then I fill in "options[3][quantity]" with "2"
    When I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Grand logo"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    When I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    And I check the "form.payment_choice.children.paymentMode.bank_check" radio
    When I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"
