# Pricing Configuration User Guide

## Overview

This guide explains how to configure apartment pricing in the EasyRent system to ensure accurate payment calculations. The system supports two main pricing models: **Total Pricing** and **Monthly Pricing**.

## Understanding Pricing Types

### Total Pricing
- **When to use**: Fixed-term rentals, all-inclusive packages, annual leases
- **How it works**: The apartment price represents the complete rental amount
- **Calculation**: Payment total = Apartment price (no multiplication)
- **Example**: ₦500,000 apartment for 12 months = ₦500,000 total

### Monthly Pricing
- **When to use**: Traditional monthly rentals, flexible lease terms
- **How it works**: The apartment price represents the monthly rental amount
- **Calculation**: Payment total = Apartment price × Rental duration
- **Example**: ₦50,000/month apartment for 12 months = ₦600,000 total

## Setting Up Apartment Pricing

### For Property Managers

#### 1. Access Apartment Management
1. Log in to your EasyRent dashboard
2. Navigate to **My Properties**
3. Select the property containing the apartment
4. Click **Edit** next to the apartment you want to configure

#### 2. Configure Pricing Type
1. In the apartment edit form, locate the **Pricing Configuration** section
2. Select the appropriate **Pricing Type**:
   - **Total**: For fixed-amount rentals
   - **Monthly**: For monthly rental amounts
3. Enter the **Apartment Price** based on your selected pricing type
4. Save the changes

#### 3. Verify Configuration
1. Generate a test proforma to verify calculations
2. Check that the payment total matches your expectations
3. Test with different rental durations if using monthly pricing

### For Administrators

#### 1. Bulk Pricing Configuration
1. Navigate to **Admin Panel** → **Pricing Configuration**
2. Use the **Bulk Update** feature to set pricing types for multiple apartments
3. Filter apartments by property, location, or type
4. Select apartments and apply pricing configuration changes

#### 2. Default Pricing Rules
1. Go to **System Configuration** → **Payment Calculation**
2. Set the **Default Pricing Type** for new apartments
3. Configure validation rules and limits
4. Save configuration changes

## Pricing Configuration Examples

### Example 1: Annual Lease (Total Pricing)
```
Apartment: 3-bedroom flat in Victoria Island
Annual Rent: ₦1,200,000
Pricing Type: Total
Rental Duration: 12 months
Calculation: ₦1,200,000 (no multiplication)
Result: ₦1,200,000 total payment
```

### Example 2: Monthly Rental (Monthly Pricing)
```
Apartment: 2-bedroom flat in Lekki
Monthly Rent: ₦80,000
Pricing Type: Monthly
Rental Duration: 6 months
Calculation: ₦80,000 × 6 months
Result: ₦480,000 total payment
```

### Example 3: Flexible Lease (Monthly Pricing)
```
Apartment: Studio apartment in Ikeja
Monthly Rent: ₦35,000
Pricing Type: Monthly
Rental Duration: 18 months
Calculation: ₦35,000 × 18 months
Result: ₦630,000 total payment
```

## Common Scenarios and Best Practices

### Scenario 1: Converting from Annual to Monthly Pricing
**Situation**: You have apartments listed with annual amounts but want to offer monthly flexibility.

**Steps**:
1. Calculate the monthly equivalent: Annual amount ÷ 12
2. Update the apartment price to the monthly amount
3. Change pricing type to "Monthly"
4. Test calculations with different durations

**Example**:
- Current: ₦1,200,000 annual (Total pricing)
- New: ₦100,000 monthly (Monthly pricing)
- 12-month rental: ₦100,000 × 12 = ₦1,200,000 ✓

### Scenario 2: Promotional Pricing
**Situation**: Offering discounted rates for longer lease terms.

**Approach 1 - Total Pricing**:
1. Calculate the discounted total amount
2. Set pricing type to "Total"
3. The same amount applies regardless of duration

**Approach 2 - Monthly Pricing with Manual Adjustment**:
1. Set the discounted monthly rate
2. Use "Monthly" pricing type
3. System calculates total automatically

### Scenario 3: Service Charges and Additional Fees
**Situation**: Apartments have base rent plus service charges.

**Recommended Approach**:
1. Set the base rent with appropriate pricing type
2. Use the "Additional Charges" feature in proforma generation
3. Service charges, legal fees, and deposits are added separately
4. System calculates the grand total including all charges

## Proforma Generation

### Creating Accurate Proformas

#### 1. Standard Proforma
1. Navigate to **Proforma Management**
2. Select the apartment (pricing configuration is automatically loaded)
3. Enter rental duration
4. Add any additional charges:
   - Service charge
   - Legal fee
   - Security deposit
   - Agency fee
5. Generate proforma

#### 2. Proforma Verification
Before sending to tenants:
1. **Check Calculation Method**: Verify the calculation method shown
2. **Review Total Amount**: Ensure it matches your expectations
3. **Validate Duration**: Confirm rental period is correct
4. **Additional Charges**: Verify all fees are included

### Proforma Calculation Breakdown
The system provides a detailed breakdown showing:
- Base rental calculation method
- Apartment price and duration used
- Additional charges itemized
- Final total amount

## EasyRent Invitation Links

### How Pricing Affects Invitations

When you send EasyRent invitation links to tenants:
1. **Payment Preview**: Shows the same calculation as proformas
2. **Consistency**: Uses identical pricing configuration
3. **Transparency**: Displays calculation method to tenant
4. **Accuracy**: Prevents calculation discrepancies

### Invitation Best Practices
1. **Verify Pricing**: Check apartment pricing before sending invitations
2. **Test Links**: Use test invitations to verify calculations
3. **Clear Communication**: Explain pricing structure to tenants
4. **Monitor Payments**: Track successful payments and any issues

## Troubleshooting Common Issues

### Issue 1: Payment Total Seems Too High
**Possible Causes**:
- Monthly pricing applied to annual amount
- Incorrect rental duration entered
- Additional charges not accounted for

**Solutions**:
1. Check apartment pricing type configuration
2. Verify the apartment price amount
3. Confirm rental duration is correct
4. Review calculation breakdown in proforma

### Issue 2: Payment Total Seems Too Low
**Possible Causes**:
- Total pricing applied to monthly amount
- Missing additional charges
- Incorrect apartment price

**Solutions**:
1. Verify pricing type matches your intent
2. Check if apartment price needs updating
3. Ensure all required charges are included

### Issue 3: Inconsistent Calculations
**Possible Causes**:
- Different pricing configurations across system
- Cache issues with recent changes
- Manual calculation errors

**Solutions**:
1. Use only system-generated calculations
2. Clear browser cache and refresh
3. Contact support if issues persist

### Issue 4: Cannot Change Pricing Type
**Possible Causes**:
- Insufficient permissions
- Existing proformas or payments
- System restrictions

**Solutions**:
1. Check user permissions with administrator
2. Create new apartment listing if needed
3. Contact technical support for assistance

## Validation and Limits

### System Limits
- **Maximum Apartment Price**: ₦999,999,999.99
- **Minimum Apartment Price**: ₦0.01 (₦0.00 allowed for promotional offers)
- **Maximum Rental Duration**: 120 months (10 years)
- **Calculation Precision**: 2 decimal places (kobo precision)

### Validation Rules
- Apartment price must be a positive number
- Rental duration must be a whole number of months
- Pricing type must be either "Total" or "Monthly"
- Final calculation cannot exceed system limits

### Error Messages
The system provides clear error messages for:
- Invalid apartment prices
- Excessive rental durations
- Calculation overflow errors
- Configuration validation failures

## Reporting and Analytics

### Pricing Configuration Reports
Access reports showing:
- Apartments by pricing type
- Average rental amounts by location
- Pricing configuration changes over time
- Calculation accuracy metrics

### Performance Monitoring
Monitor:
- Calculation response times
- Error rates by pricing type
- Cache performance statistics
- User adoption of pricing features

## Migration from Legacy System

### If You Have Existing Apartments

#### Step 1: Audit Current Pricing
1. Review all apartment listings
2. Identify pricing structure (annual vs monthly)
3. Note any inconsistencies or errors

#### Step 2: Configure Pricing Types
1. For annual amounts: Set pricing type to "Total"
2. For monthly amounts: Set pricing type to "Monthly"
3. Update apartment prices if needed

#### Step 3: Test and Verify
1. Generate test proformas for each apartment
2. Compare with previous calculations
3. Verify accuracy across different durations

#### Step 4: Train Users
1. Educate property managers on new system
2. Provide training on pricing configuration
3. Share this user guide with relevant staff

## Support and Help

### Getting Help
- **User Manual**: Refer to this guide for common questions
- **Video Tutorials**: Available in the help section
- **Support Ticket**: Submit tickets for technical issues
- **Training Sessions**: Request training for your team

### Contact Information
- **Technical Support**: support@easyrent.com
- **Training Requests**: training@easyrent.com
- **Feature Requests**: feedback@easyrent.com

### Best Practices Summary
1. ✅ Always set appropriate pricing type for each apartment
2. ✅ Test calculations before sending to tenants
3. ✅ Use system-generated proformas for accuracy
4. ✅ Monitor payment calculations for consistency
5. ✅ Keep apartment pricing information up to date
6. ✅ Train staff on proper pricing configuration
7. ✅ Report any calculation discrepancies immediately

---

**Last Updated**: December 2024  
**Version**: 1.0  
**For Questions**: Contact EasyRent Support Team