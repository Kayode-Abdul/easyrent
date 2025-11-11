@include('header')

<div class="content">
    <div class="container pt-pad">    
        <!-- Hero Section -->
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card bg-gradient-info text-white">
                    <div class="card-body text-center py-5">
                        <h1 class="display-4 mb-3">
                            <i class="nc-icon nc-bulb-63"></i> Frequently Asked Questions
                        </h1>
                        <p class="lead">Find answers to common questions about EasyRent</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search FAQ -->
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <div class="input-group">
                    <input type="text" class="form-control form-control-lg" id="faqSearch" placeholder="Search FAQs...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="nc-icon nc-zoom-split"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Categories -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <button class="btn btn-outline-primary mr-2 mb-2 faq-filter" data-category="all">All</button>
                            <button class="btn btn-outline-primary mr-2 mb-2 faq-filter" data-category="general">General</button>
                            <button class="btn btn-outline-primary mr-2 mb-2 faq-filter" data-category="landlord">Landlords</button>
                            <button class="btn btn-outline-primary mr-2 mb-2 faq-filter" data-category="tenant">Tenants</button>
                            <button class="btn btn-outline-primary mr-2 mb-2 faq-filter" data-category="payment">Payments</button>
                            <button class="btn btn-outline-primary mr-2 mb-2 faq-filter" data-category="property-manager">Property Managers</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Accordion -->
        <div class="row">
            <div class="col-md-12">
                <div class="accordion" id="faqAccordion">
                    
                    <!-- General Questions -->
                    <div class="card faq-item" data-category="general">
                        <div class="card-header" id="faq1">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse1">
                                    <i class="nc-icon nc-simple-add"></i> What is EasyRent?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse1" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                EasyRent is Nigeria's leading property rental platform that connects landlords, tenants, and property managers. We provide a comprehensive solution for property listing, tenant screening, rent collection, and property management services across all 36 states in Nigeria.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="general">
                        <div class="card-header" id="faq2">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse2">
                                    <i class="nc-icon nc-simple-add"></i> How do I create an account?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse2" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Creating an account is simple! Click on "Sign Up" at the top of the page, fill in your details including your name, email, phone number, and choose a secure password. You'll receive a verification email to activate your account. Once verified, you can start using EasyRent immediately.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="general">
                        <div class="card-header" id="faq3">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse3">
                                    <i class="nc-icon nc-simple-add"></i> Is EasyRent free to use?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse3" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Yes! Creating an account and browsing properties is completely free. For landlords, listing properties is also free. We only charge a small commission when a successful rental is completed through our platform. Tenants can search and apply for properties at no cost.
                            </div>
                        </div>
                    </div>

                    <!-- Landlord Questions -->
                    <div class="card faq-item" data-category="landlord">
                        <div class="card-header" id="faq4">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse4">
                                    <i class="nc-icon nc-simple-add"></i> How do I list my property?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse4" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                After logging into your landlord account, click on "Add New Property" in your dashboard. Fill in the property details including location, type, rent amount, and upload high-quality photos. Our team will verify your listing within 24 hours, and it will go live on the platform.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="landlord">
                        <div class="card-header" id="faq5">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse5">
                                    <i class="nc-icon nc-simple-add"></i> What commission do you charge?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse5" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Our commission structure is transparent and competitive. We charge a small percentage of the annual rent only when a tenant is successfully matched with your property. The exact rate depends on your location and the services used. You can view the detailed commission breakdown in your dashboard.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="landlord">
                        <div class="card-header" id="faq6">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse6">
                                    <i class="nc-icon nc-simple-add"></i> How are tenants verified?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse6" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                All tenants go through our comprehensive verification process including identity verification with valid ID cards, employment verification, previous landlord references, and credit checks where applicable. We also verify their phone numbers and email addresses to ensure authenticity.
                            </div>
                        </div>
                    </div>

                    <!-- Tenant Questions -->
                    <div class="card faq-item" data-category="tenant">
                        <div class="card-header" id="faq7">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse7">
                                    <i class="nc-icon nc-simple-add"></i> How do I search for properties?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse7" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Use our advanced search filters to find properties by location, price range, property type, number of bedrooms, and amenities. You can also save your search preferences and get notifications when new properties matching your criteria become available.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="tenant">
                        <div class="card-header" id="faq8">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse8">
                                    <i class="nc-icon nc-simple-add"></i> What documents do I need to rent a property?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse8" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Typically, you'll need a valid government-issued ID, proof of income (salary slip or bank statements), previous landlord reference (if applicable), and passport photographs. Some landlords may require additional documents like guarantor information or employment letter.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="tenant">
                        <div class="card-header" id="faq9">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse9">
                                    <i class="nc-icon nc-simple-add"></i> Can I schedule property viewings?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse9" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Yes! You can schedule property viewings directly through the platform. Contact the landlord or property manager through our messaging system to arrange a convenient viewing time. We recommend viewing properties in person before making any commitments.
                            </div>
                        </div>
                    </div>

                    <!-- Payment Questions -->
                    <div class="card faq-item" data-category="payment">
                        <div class="card-header" id="faq10">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse10">
                                    <i class="nc-icon nc-simple-add"></i> What payment methods do you accept?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse10" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                We accept various payment methods including bank transfers, debit cards, credit cards, and mobile money payments. All payments are processed securely through our encrypted payment gateway partners including Paystack and Flutterwave.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="payment">
                        <div class="card-header" id="faq11">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse11">
                                    <i class="nc-icon nc-simple-add"></i> How secure are online payments?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse11" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                All payments are processed using bank-level security with SSL encryption. We never store your card details on our servers. All transactions are monitored for fraud, and we use PCI DSS compliant payment processors to ensure maximum security.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="payment">
                        <div class="card-header" id="faq12">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse12">
                                    <i class="nc-icon nc-simple-add"></i> Can I set up automatic rent payments?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse12" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Yes! You can set up automatic monthly rent payments to ensure you never miss a payment. You'll receive notifications before each payment is processed, and you can modify or cancel the automatic payment at any time from your dashboard.
                            </div>
                        </div>
                    </div>

                    <!-- Property Manager Questions -->
                    <div class="card faq-item" data-category="property-manager">
                        <div class="card-header" id="faq13">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse13">
                                    <i class="nc-icon nc-simple-add"></i> How do I become a property manager on EasyRent?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse13" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                To become a certified property manager, you need to complete our application process which includes background verification, professional references, and completion of our property management training program. Once approved, you'll have access to our property manager dashboard and can start managing properties.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="property-manager">
                        <div class="card-header" id="faq14">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse14">
                                    <i class="nc-icon nc-simple-add"></i> What services can property managers offer?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse14" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Property managers can offer various services including tenant screening, rent collection, property maintenance coordination, lease management, property inspections, and tenant relations. You can customize your service offerings based on your expertise and landlord requirements.
                            </div>
                        </div>
                    </div>

                    <div class="card faq-item" data-category="property-manager">
                        <div class="card-header" id="faq15">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse15">
                                    <i class="nc-icon nc-simple-add"></i> How are property manager commissions calculated?
                                </button>
                            </h5>
                        </div>
                        <div id="collapse15" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                Property manager commissions are calculated based on the services provided and the rental value of managed properties. You can view detailed commission breakdowns in your analytics dashboard, including earnings from different service categories and performance bonuses.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body text-center py-4">
                        <h3 class="mb-3">Still Have Questions?</h3>
                        <p class="lead mb-4">Can't find what you're looking for? Our support team is here to help!</p>
                        <div>
                            <a href="{{ route('contact') }}" class="btn btn-light btn-lg mr-3">
                                <i class="nc-icon nc-email-85"></i> Contact Support
                            </a>
                            <a href="tel:+2348000000000" class="btn btn-outline-light btn-lg">
                                <i class="nc-icon nc-mobile"></i> Call Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<style>
.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #51cbce, #6bd098) !important;
}

.faq-item {
    margin-bottom: 10px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.faq-item .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.faq-item .btn-link {
    color: #495057;
    text-decoration: none;
    font-weight: 500;
    width: 100%;
    text-align: left;
}

.faq-item .btn-link:hover {
    color: #007bff;
    text-decoration: none;
}

.faq-item .btn-link i {
    margin-right: 10px;
    transition: transform 0.3s ease;
}

.faq-item .btn-link[aria-expanded="true"] i {
    transform: rotate(45deg);
}

.faq-filter.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

#faqSearch {
    border-radius: 25px;
}

.input-group-text {
    border-radius: 0 25px 25px 0;
}
</style>

<script>
$(document).ready(function() {
    // FAQ Search functionality
    $('#faqSearch').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.faq-item').each(function() {
            var faqText = $(this).text().toLowerCase();
            if (faqText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // FAQ Category filtering
    $('.faq-filter').on('click', function() {
        var category = $(this).data('category');
        
        // Update active button
        $('.faq-filter').removeClass('active');
        $(this).addClass('active');
        
        // Filter FAQ items
        if (category === 'all') {
            $('.faq-item').show();
        } else {
            $('.faq-item').hide();
            $('.faq-item[data-category="' + category + '"]').show();
        }
        
        // Clear search
        $('#faqSearch').val('');
    });
    
    // Set default active filter
    $('.faq-filter[data-category="all"]').addClass('active');
});
</script>

@include('footer')