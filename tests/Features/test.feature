Feature:
    Given I have an api
    As a developer
    I want to test it

    Scenario: 200 user response, authentic ira
        Given I set the following headers:
            | content-type | application/json |
            | accept       | en               |
        When I make a GET request to "User" endpoint
        Then I expect a 200 "User" response
        And the response should match the snapshot
        And I expect the following content in the JSON response 'address.jug' key:
            """
            "15"
            """

    Scenario: 500 exception response
        When I make a GET request to "User" endpoint with query string "exception=1&error=message+is+bad&errorCode=503"
        Then the response should match the snapshot
        And I expect a 500 "User" response

    Scenario: 201 Get request'si in spade
        When I make a GET request to "User" endpoint with query string "test=true"
        Then the response should match the snapshot
        And I expect a 201 "User" response

    Scenario: 200 POST request to create user
        When I make a POST request to "User" endpoint with body:
            """
            {"name": "Wahab Qureshi", "postcode": "B23 7QQ"}
            """
        Then I expect a 200 "User" response
        And the response should match the snapshot
        