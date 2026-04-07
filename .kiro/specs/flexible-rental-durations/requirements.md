# Requirements Document

## Introduction

EasyRent currently supports only monthly-based rental durations (1, 3, 6, 12 months). This feature will extend the platform to support all rental duration types: hourly, daily, weekly, monthly, and yearly rentals. This enables landlords to list properties for short-term rentals (event spaces, warehouses, meeting rooms) and long-term leases, making EasyRent a comprehensive rental platform for all property types and use cases.

## Glossary

- **Rental Duration System**: The system that manages and calculates rental periods and pricing across different time units
- **Duration Unit**: The time measurement for a rental period (hour, day, week, month, year)
- **Rental Type**: The category of rental offering (hourly, daily, weekly, monthly, yearly)
- **Flexible Pricing**: The ability to set different prices for different duration units on the same property
- **Availability Calendar**: A system that tracks when properties are available or booked for specific time periods
- **Duration Conversion**: The process of converting between different time units for calculations
- **Payment System**: The existing Paystack-integrated payment processing infrastructure
- **Proforma Receipt**: An invoice document generated for rental payments
- **Apartment**: A rentable unit within a property
- **Benefactor System**: The existing recurring payment system that supports monthly, quarterly, and annual frequencies

## Requirements

### Requirement 1

**User Story:** As a landlord, I want to specify what rental duration types my property supports, so that I can offer hourly, daily, weekly, monthly, or yearly rentals based on my property type.

#### Acceptance Criteria

1. WHEN a landlord creates or edits a property THEN the system SHALL display rental type options (hourly, daily, weekly, monthly, yearly)
2. WHEN a landlord selects rental types THEN the system SHALL allow multiple rental type selections for the same property
3. WHEN rental types are saved THEN the system SHALL persist the rental type configuration to the database
4. WHEN a property has multiple rental types THEN the system SHALL display all available rental options to potential tenants

### Requirement 2

**User Story:** As a landlord, I want to set different prices for different rental durations, so that I can charge appropriately for hourly vs monthly rentals.

#### Acceptance Criteria

1. WHEN a landlord enables a rental type THEN the system SHALL display a price input field for that duration unit
2. WHEN a landlord enters prices for multiple duration types THEN the system SHALL validate that all prices are positive numbers
3. WHEN pricing is saved THEN the system SHALL store each price with its corresponding duration unit
4. WHEN displaying property listings THEN the system SHALL show the price per selected duration unit

### Requirement 3

**User Story:** As a tenant, I want to search for properties by rental duration type, so that I can find properties available for my desired rental period.

#### Acceptance Criteria

1. WHEN a tenant views the property search page THEN the system SHALL display rental type filter options
2. WHEN a tenant selects a rental type filter THEN the system SHALL return only properties that support that rental type
3. WHEN search results are displayed THEN the system SHALL show the price for the selected rental duration
4. WHEN no rental type is selected THEN the system SHALL display all properties with their default pricing

### Requirement 4

**User Story:** As a tenant, I want to select my desired rental duration when booking, so that I can rent a property for the exact time period I need.

#### Acceptance Criteria

1. WHEN a tenant views a property THEN the system SHALL display all available rental duration options for that property
2. WHEN a tenant selects a duration type and enters a quantity THEN the system SHALL calculate the total rental cost
3. WHEN the calculation is complete THEN the system SHALL display the start date, end date, and total amount
4. WHEN a tenant changes the duration or quantity THEN the system SHALL recalculate the end date and total amount immediately

### Requirement 5

**User Story:** As a system, I want to track rental durations with their specific time units, so that payment calculations and lease tracking are accurate across all rental types.

#### Acceptance Criteria

1. WHEN a payment record is created THEN the system SHALL store both the duration value and duration unit
2. WHEN a proforma receipt is generated THEN the system SHALL include the duration value and duration unit
3. WHEN an apartment invitation is created THEN the system SHALL store the lease duration with its time unit
4. WHEN calculating payment amounts THEN the system SHALL use the duration value and unit to determine the correct price

### Requirement 6

**User Story:** As a system, I want to convert between different time units accurately, so that date calculations and comparisons work correctly across all rental types.

#### Acceptance Criteria

1. WHEN converting hours to dates THEN the system SHALL calculate the end datetime by adding the specified hours to the start datetime
2. WHEN converting days to dates THEN the system SHALL calculate the end date by adding the specified days to the start date
3. WHEN converting weeks to dates THEN the system SHALL calculate the end date by adding seven times the week count to the start date
4. WHEN converting months to dates THEN the system SHALL calculate the end date by adding the specified months to the start date
5. WHEN converting years to dates THEN the system SHALL calculate the end date by adding the specified years to the start date

### Requirement 7

**User Story:** As a landlord, I want the system to check availability before accepting bookings, so that I don't have double bookings for the same time period.

#### Acceptance Criteria

1. WHEN a tenant attempts to book a property THEN the system SHALL check for existing bookings that overlap the requested time period
2. WHEN an overlap is detected THEN the system SHALL prevent the booking and display an error message
3. WHEN no overlap exists THEN the system SHALL allow the booking to proceed
4. WHEN checking availability THEN the system SHALL consider the specific time units (hours for hourly rentals, days for daily rentals)

### Requirement 8

**User Story:** As a tenant, I want to see a calendar showing property availability, so that I can choose dates when the property is available.

#### Acceptance Criteria

1. WHEN a tenant views a property with hourly rentals THEN the system SHALL display a date and time picker
2. WHEN a tenant views a property with daily or longer rentals THEN the system SHALL display a date range picker
3. WHEN displaying the calendar THEN the system SHALL mark unavailable dates or times as disabled
4. WHEN a tenant selects dates THEN the system SHALL validate that the selected period is available

### Requirement 9

**User Story:** As a system administrator, I want commission rates to apply correctly across all rental types, so that the platform earns appropriate revenue from all rental durations.

#### Acceptance Criteria

1. WHEN calculating commissions for a payment THEN the system SHALL apply the commission rate to the total rental amount
2. WHEN a payment is completed THEN the system SHALL record the commission amount with the payment record
3. WHEN generating financial reports THEN the system SHALL include commissions from all rental duration types
4. WHEN commission rates are updated THEN the system SHALL apply new rates to future transactions only

### Requirement 10

**User Story:** As a landlord, I want to set minimum and maximum rental periods, so that I can control how my property is rented.

#### Acceptance Criteria

1. WHEN a landlord configures a rental type THEN the system SHALL allow setting a minimum rental quantity
2. WHEN a landlord configures a rental type THEN the system SHALL allow setting a maximum rental quantity
3. WHEN a tenant attempts to book below the minimum THEN the system SHALL prevent the booking and display the minimum requirement
4. WHEN a tenant attempts to book above the maximum THEN the system SHALL prevent the booking and display the maximum limit

### Requirement 11

**User Story:** As a system, I want to maintain backward compatibility with existing monthly rentals, so that current landlords and tenants are not disrupted.

#### Acceptance Criteria

1. WHEN existing apartment records are accessed THEN the system SHALL treat null duration_unit values as monthly
2. WHEN existing payment records are processed THEN the system SHALL default to monthly duration for records without a duration_unit
3. WHEN displaying existing properties THEN the system SHALL show monthly as the default rental type
4. WHEN migrating data THEN the system SHALL preserve all existing rental amounts and durations

### Requirement 12

**User Story:** As a tenant, I want to receive proforma receipts that clearly show my rental duration and pricing, so that I understand what I'm paying for.

#### Acceptance Criteria

1. WHEN a proforma receipt is generated THEN the system SHALL display the duration value and unit (e.g., "3 days", "2 weeks")
2. WHEN the receipt shows pricing THEN the system SHALL display the price per unit and the total amount
3. WHEN the receipt shows dates THEN the system SHALL display the start date, end date, and total rental period
4. WHEN a tenant views the receipt THEN the system SHALL format all information in a clear, readable manner
