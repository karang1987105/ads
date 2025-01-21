<x-page-layout>
    <x-slot name="page_title">Privacy Policy</x-slot>
    <style>
        .landing-form h4 {
            color: #a2c6ff !important;
        }

        .landing-form ul.list-group {
            list-style-type: none;
        }
    </style>
    <div class="row justify-content-center pt-5 pb-5">
        <div class="card card-small col-md-11 landing-form">
            <div class="card-header border-bottom row" style="background-color: #ffffff12;">
                <h3 class="col mb-0 text-center">Privacy Policy</h3>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    <p class="bold green">Last updated on January 24th, 2023.</p>
                    <h4 class="bold">Privacy Policy</h4>
                    This Privacy Policy document covers general procedures on the collection, use and disclosure of Your information when You use
                    the Service and tells You about Your privacy rights and how the law protects You. Further it covers a Cookie Policy, the GDPR
                    Policy and the CCPA Policy. This Privacy Policy document is also part of the <a href="{{ route('terms-of-service') }}">Terms Of
                        Service</a>. This Privacy Policy applies only to Our online activities and is valid for visitors to Our Service with regards to
                    the information that they share and collect. This Privacy Policy is not applicable to any information collected offline or via
                    channels other than this Service. By using Our Service You are accepting this Privacy Policy. If You do not agree to the Privacy
                    Policy, please do not use Our Service.
                    </p>
                    <h4 class="bold">Amendments And Acceptance</h4>
                    We may change Our Privacy Policy at any time by posting a revised Privacy Policy on this page or sending information
                    regarding the changes to the email address You provide to Us during registration. You are responsible for regularly reviewing
                    this page to obtain timely notice of possible changes. The easiest way to do so is to check the date on top on which the
                    Privacy Policy has been updated. It is assumed that You have read and accepted the changes by continued use of this
                    Service after the changes have been posted or information about them has been sent to You. If You have additional questions
                    or require more information about Our Privacy Policy, do not hesitate to <a href="{{ route('contact.create') }}">contact</a> Us.
                    </p>
                    <h4 id="table-of-contents" class="bold toc-pos">Table Of Contents</h4>
                    &#10147; <a href="#definitions">Definitions</a><br>
                    </p>
                    <h4 id="definitions" class="bold toc-pos">Definitions</h4>
                    Definition of capitalized words for the purposes of this Privacy Policy. The following definitions shall have the same meaning
                    regardless of whether they appear in singular or in plural. Capitalized words not defined in this Privacy Policy have the meaning
                    as defined in the Terms Of Service.
                    </p>
                    <span class="bold">"Company"</span><br>
                    Referred to as either "the Company", "We", "Us" or "Our" in this Privacy Policy refers to {{ env('APP_NAME') }}.
                    </p>
                    <span class="bold">"Service"</span><br>
                    Refers to this web application accessed from <a href="{{ env('APP_URL')}}">{{ env('APP_URL')}}</a>.
                    </p>
                    <span class="bold">"Account"</span><br>
                    Means a unique Account created for You to access Our Service or parts of Our Service.
                    </p>
                    <span class="bold">"Data Controller"</span><br>
                    For the purposes of General Data Protection Regulation, the GDPR. Refers to the Company as the legal person which alone or jointly
                    with others determines the purposes and means of the processing of Personal Information.
                    </p>
                    <span class="bold">"You"</span><br>
                    Means the individual accessing or using the Service, the Company or other legal entity on behalf of which such individual is
                    accessing or using the Service as applicable. Under GDPR, You can be referred to as the Data Subject or as the User as You are the
                    individual using the Service.
                    </p>
                    <span class="bold">"Consumer"</span><br>
                    For the purpose of the CCPA, means a natural person who is a California resident. A resident as defined in the law, includes every
                    individual who is in the USA for other than a temporary or transitory purpose and every individual who is domiciled in the USA
                    who is outside the USA for a temporary or transitory purpose.
                    </p>
                    <span class="bold">"Business"</span><br>
                    For the purpose of California Consumer Privacy Act, the CCPA. Refers to the Company as the legal entity that collects Consumer's
                    Personal Information and determines the purposes and means of the processing of Consumer's Personal Information or on behalf of
                    which such information is collected and that alone or jointly with others determines the purposes and means of the processing of
                    Consumer's Personal Information that does Business in the State of California.
                    </p>
                    <span class="bold">"Personal Information"</span><br>
                    Is any information that relates to an identified or identifiable individual. For the purposes of the CCPA, Personal Information
                    means any information that identifies, relates to, describes or is capable of being associated with or could reasonably be linked
                    directly or indirectly with You. For the purposes of GDPR, Personal Information means any information relating to You such as a
                    name, an identification number, location data, online identifier or to one or more factors specific to the physical, physiological,
                    genetic, mental, economic, cultural or social identity.
                    </p>
                    <span class="bold">"Country"</span><br>
                    Refers to Country of residence.
                    </p>
                    <span class="bold">"Device"</span><br>
                    Refers to any Device that can access the Service such as a computer, a cellphone or a digital tablet.
                    </p>
                    <span class="bold">"Service Provider"</span><br>
                    Means any natural or legal person who processes the data on behalf of the Company. It refers to third party companies or individuals
                    employed by the Company to facilitate the Service, to provide the Service on behalf of the Company, to perform services related to
                    the Service or to assist the Company in analyzing how the Service is used. For the purpose of the GDPR, Service Providers are
                    considered Data Controllers.
                    </p>
                    <span class="bold">"Usage Data"</span><br>
                    Refers to data collected automatically either generated by the use of the Service or from the Service infrastructure itself.
                    </p>
        </div>
    </div>
    <button onclick="location.href='#table-of-contents'" class="btn-top">&#10146;</button>
</x-page-layout>