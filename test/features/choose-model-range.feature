Feature: Choose Model Range

    Scenario: Can only select model ranges fitting customer criteria
        Given there are model ranges
        And I have specified criteria that include at least one model range
        When I view model ranges
        Then I should only be able to select model ranges that fit my criteria

    Scenario: Can not select out of stock model ranges
        Given there are out of stock model ranges
        And I have specified criteria that include the out of stock model ranges
        When I view model ranges
        Then I should not be able to select out of stock model ranges

    Scenario: Can not see end of line model ranges
        Given there are end of line model ranges
        And I have specified criteria that include the end of line model ranges
        When I view model ranges
        Then I should not be able to see end of line model ranges

    Scenario: Customer criteria does not include any model ranges
        Given there are model ranges
        And I have specified criteria that does not include any model ranges
        When I view model ranges
        Then I should not be able to select any model ranges

    Scenario: User can not proceed without selecting at least one model range
        Given there are model ranges
        And I have specified criteria that include at least one model range
        When I view model ranges
        Then I should not be able to proceed without selecting a model range

    Scenario: User should see indication as to why out of stock model ranges are not selectable
        Given there are out of stock model ranges
        And I have specified criteria that include the out of stock model ranges
        When I view model ranges
        Then I should see an explanation for why I can not select out of stock model ranges

    Scenario: User should see indication as to why model ranges outside of criteria are not selectable
        Given there are model ranges
        And I have specified criteria that excludes at least one model range
        When I view model ranges
        Then I should see an explanation for why I can not select model ranges outside of my criteria

    Scenario: Model ranges have title and default image
        Given there are model ranges
        And I have specified criteria that include at least one model range
        When I view model ranges
        Then I should see the default image and title for displayed model ranges