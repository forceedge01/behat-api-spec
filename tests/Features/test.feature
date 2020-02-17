Feature:
    Given I have an api
    As a developer
    I want to test it

    Scenario:
        Given I set the following headers:
            | content-type | application/json |
            | accept       | en               |
        When I make a POST request to "User" endpoint with body:
            """

            """
        Then I expect a 200 "User" response

    Scenario:
        When I make a GET request to "User" endpoint with query string "exception=1"
        Then I expect a 500 "User" response

    Scenario:
        When I make a GET request to "User" endpoint with query string "test=1"
        Then I expect a 201 "User" response
