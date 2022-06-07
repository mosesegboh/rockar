Feature: Register account

Scenario: User can register account
  Given I have an option to register a new account 
  When I register a new account in GCDM
  Then my Rockar user account should be created
  And I should be logged into my Rockar account

