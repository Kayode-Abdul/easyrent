# Requirements Document

## Introduction

The EasyRent system currently has incorrect payment calculation logic when generating proformas and processing EasyRent invitation payments. The system incorrectly treats apartment prices as monthly amounts and multiplies them by rental duration, leading to inflated payment totals that do not reflect the actual intended pricing structure.

## Glossary

- **Proforma_System**: The component responsible for generating payment proformas and calculating totals
- **Apartment_Price**: The base price amount stored for an apartment unit
- **Payment_Total**: The final calculated amount for a rental payment
- **EasyRent_Invitation**: The invitation system that allows tenants to apply for apartments with payment previews
- **Rental_Duration**: The number of months for a rental period

## Requirements

### Requirement 1

**User Story:** As a landlord, I want proforma calculations to use correct pricing logic, so that payment totals accurately reflect my intended rental amounts.

#### Acceptance Criteria

1. WHEN the Proforma_System calculates payment totals THEN the system SHALL use the Apartment_Price as the base amount without automatic monthly multiplication
2. WHEN a proforma is generated for any rental duration THEN the Proforma_System SHALL apply pricing rules consistently based on the apartment's configured pricing structure
3. WHEN the Apartment_Price represents a total rental amount THEN the Proforma_System SHALL not multiply by Rental_Duration
4. WHEN the Apartment_Price represents a monthly amount THEN the Proforma_System SHALL multiply by Rental_Duration only if explicitly configured
5. WHEN payment calculations are performed THEN the Proforma_System SHALL maintain data integrity and produce consistent results

### Requirement 2

**User Story:** As a tenant, I want to see accurate payment amounts in EasyRent invitations, so that I can make informed decisions about rental applications.

#### Acceptance Criteria

1. WHEN an EasyRent_Invitation displays payment preview THEN the system SHALL show the correct Payment_Total based on proper calculation logic
2. WHEN a tenant views invitation payment details THEN the system SHALL display amounts that match the actual proforma calculations
3. WHEN payment totals are calculated for invitations THEN the system SHALL use the same logic as proforma generation
4. WHEN invitation payment amounts are displayed THEN the system SHALL clearly indicate the pricing structure being used
5. WHEN tenants proceed with payments THEN the Payment_Total SHALL match the previewed amount exactly

### Requirement 3

**User Story:** As a system administrator, I want payment calculation logic to be configurable and transparent, so that different pricing models can be supported accurately.

#### Acceptance Criteria

1. WHEN apartment pricing is configured THEN the system SHALL clearly distinguish between total amounts and monthly amounts
2. WHEN payment calculations are performed THEN the Proforma_System SHALL log calculation steps for audit purposes
3. WHEN pricing configuration changes THEN the system SHALL apply new logic to future calculations without affecting existing records
4. WHEN calculation errors occur THEN the system SHALL provide clear error messages indicating the specific calculation issue
5. WHEN administrators review payment calculations THEN the system SHALL provide detailed breakdown of how totals were derived

### Requirement 4

**User Story:** As a developer, I want payment calculation logic to be centralized and testable, so that calculation bugs can be prevented and easily fixed.

#### Acceptance Criteria

1. WHEN payment calculations are needed THEN the system SHALL use a single, centralized calculation service
2. WHEN calculation logic is modified THEN the system SHALL maintain backward compatibility for existing payment records
3. WHEN new pricing models are added THEN the system SHALL extend the calculation service without breaking existing functionality
4. WHEN calculation methods are called THEN the system SHALL validate input parameters and return consistent results
5. WHEN payment calculations are tested THEN the system SHALL provide comprehensive test coverage for all pricing scenarios