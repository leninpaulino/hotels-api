# Hotels API

Create a basic Hotel API - Coding Challenge

## Installation

Use [Valet](https://laravel.com/docs/6.x/valet) or [Homestead](https://laravel.com/docs/6.x/homestead) to run this project.

## Usage


```bash 
docker-compose up -d
```

Visit [`localhost:8081`](http://localhost:8081/) to see the OpenAPI Specification. Use any HTTP Client tool you want for interacting with the API.

## TO-DO
The workflow I'm using for building every feature of this API is:

1. Design the API using the OpenAPI Spec
2. Write tests according to the API Specification
3. Write the code needed for making those tests pass


- [x] Create project scaffolding
- [x] Add SwaggerUI to docker-compose
- [x] Add Accommodation schema to yml
- [x] Add user authentication via API
- [x] Add Accommodations model, migration and factory
- [x] Add Locations model, migration and factory
- [x] Make users able to post an accommodation
- [x] Make users able to retrieve all accommodations
- [x] Make users able to retrieve a single accommodation
- [x] Make users able to update accommodations
- [x] Make users able to delete accomodations
- [x] Add booking endpoint than whenever is called reduces the accommodation availability, and that fails if there is no availability. 
- [x] Make all errors and exceptions to follow the RFC7807 spec
- [x] Add tests for each validation
- [x] Add installation instructions 