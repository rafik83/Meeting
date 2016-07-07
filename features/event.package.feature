@event

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
    And I should see "package.product.unavailable"
    When I fill in the following:
      | options_10 | 1 |
      | options_11 | 3 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    When I go to this page "/fr/sheet/1/package/step/3"
    Then the "options_10" field should contain "1"
    Then the "options_11" field should contain "3"

  Scenario: I can fill my billing-info
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I am on this page "/fr/sheet/1/package/step/3"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
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

  Scenario: I can see my package summary
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I am on this page "/fr/sheet/1/package/summary"
    Then I should see "package.summary.title"
    And I should see "Formule Exposant"
    And I should see "Participant supplémentaire"
    And I should see "Packs de rendez-vous"
    And I should see "Packs de rendez-vous"
    And I should see "Option D"
    And I should see "Option E"
    And I should see "package.summary.pay"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"

  Scenario: I can my payment method
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I am on this page "/fr/sheet/1/package/summary"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    And I should see "package.payment.total.toPay"
    And I check the "form.payment_choice.children.paymentMode.bank_card" radio
    Then I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"
    And I should see "order.transaction.state.pending"
