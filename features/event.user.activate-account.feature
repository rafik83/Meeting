Feature: Activate Account
  I need to be able to activate my account

  Scenario: I can activate my account
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml  |
      | UserWithActivateAccountTokenAndSheet.yml                       |
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/activate/azertyuiopqsdfghjklmwxcvbn"
    And the response status code should be 200
    Then I fill in the following:
      | form.activate_account_password.children.password.children.first.label  | newpassword |
      | form.activate_account_password.children.password.children.second.label | newpassword |
    And I press "form.activate_account_password.children.submit.label"
  #
  # Need to rewrite the way participant can fill his profile
  #
#    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/update_participant/2"
#    And the response status code should be 200
#    And I should see "flash.activate_account.success"
