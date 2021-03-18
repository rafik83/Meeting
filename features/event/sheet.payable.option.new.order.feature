@event @sheet @package @product @order
Feature: Select payable option in sheet

  Scenario: I can pay my package with payable option "Option Chaise"
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
    When I go to this page "/fr/sheet/1/package/step/1"
    Then I check radio "plans_plan_1"
    When I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Participants & plannings"
    When I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And the "options[6][quantity]" field should contain "1"
    When I press "package.product.validate"
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
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "Ajouter un logo"
    Then the response status code should be 200
    And I should not see "sheet.object.option.buyable.label"
    And I should not see "sheet_image_data_selectedProduct_6"

  Scenario: I can always select a payable option in my media because I doesn't buy one in my package
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the response status code should be 200
    And I should see "sheet.object.option.buyable.label"
    And I check radio "sheet_media_collection_data_selectedProduct_9"
    When I press "form.sheet_media_collection_data.children.submit.label"
    Then I should be on this page "/fr/sheet/1/fr"

  Scenario: I can see my new payable option "Option E" in my package
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "navigation.links.package.order_summary_total"
    Then I should be on this page "/fr/sheet/1/order/summary"
    When I follow "package.summary.edit"
    Then I go to this page "/fr/sheet/1/package/step/2"
    And the "options[11][quantity]" field should contain "1"

  Scenario: I can pay my new payable option "Option E"
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    And I go to this page "/fr/sheet/1/package/step/2"
    When I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Option E"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    When I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    And I check the "form.payment_choice.children.paymentMode.bank_check" radio
    When I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"

  Scenario: I can't edit my media object payable option after order paid
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/sheet/1"
    When I follow "sheet.object.action.edit \"Médias\""
    Then the response status code should be 200
    And I should not see "sheet.object.option.buyable.label"
    And I should not see "sheet_media_collection_data_selectedProduct_9"
