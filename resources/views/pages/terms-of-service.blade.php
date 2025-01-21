<x-page-layout>
    <x-slot name="page_title">Terms Of Service</x-slot>
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
            <div class="card-header border-bottom row" style="background-color: #ffffff12">
                <h3 class="col mb-0 text-center">Terms Of Service</h5>
            </div>
            <ul class="list-group list-group-flush mb-1" style="padding-left: 20px; padding-right: 20px">
                    <p class="bold green">Last updated on January 24, 2024</p>
                    <h4 class="bold">Terms Of Service</h4>
                    <p>
                        This Terms Of Service document sets forth the general Terms and conditions of Your use of Our Service and any of its related products
                        and services. These Terms Of Service apply to all visitors, users and others who access or use the Service. This agreement is legally
                        binding between You and {{ env('APP_NAME') }}. If You are entering into this agreement on behalf of a business or other legal entity,
                        You represent that You have the authority to bind such entity to this agreement in which case the Terms "You" or "Your" shall refer to
                        such entity. If You do not have such authority or If You do not agree to abide by the Terms of this agreement, You are not authorized
                        to access or use Our Service. By accessing and using the Service, You acknowledge that You have read, understood and agree to be bound
                        by the Terms of this agreement. You acknowledge that this agreement is a contract between You and Us, even though it is electronic and
                        is not physically signed by You and it governs Your use of the Service.
                    <h4 class="bold">Amendments And Acceptance</h4>
                    We reserve the right to modify Our Terms Of Service at any time at Our discretion by posting a revised version on this page or sending
                    information regarding the changes to the email address You provide to Us during registration. You are responsible for regularly reviewing
                    this page to obtain timely notice of possible changes. The easiest way to do so is to check the date on top on which the Terms Of Service
                    has been updated. An updated version of this agreement will be effective immediately upon the posting of the revised agreement unless
                    otherwise specified. Your continued use of Our Service after the effective date of the revised agreement or such other act specified at
                    that time will constitute Your consent to those changes. If You have additional questions or require more information about Our Terms Of
                    Service do not hesitate to <a href="{{ route('contact.create') }}">contact</a> Us.
                    </p>
                    <h4 id="table-of-contents" class="bold toc-pos">Table Of Contents</h4>
                    &#10147; <a href="#definitions">Definitions</a><br>
                    </p>
                    <h4 id="definitions" class="bold toc-pos">Definitions</h4>
                    Definition of capitalized words for the purposes of these Terms Of Service. The following definitions shall have the same meaning
                    regardless of whether they appear in singular or in plural.
                    </p>
                    <span class="bold">"Company"</span><br>
                    Referred to as either "the Company", "We", "Us" or "Our" in these Terms Of Service refers to {{ env('APP_NAME') }}.
                    </p>
                    <span class="bold">"Service"</span><br>
                    Refers to this web application accessed from <a href="{{ env('APP_URL')}}">{{ env('APP_URL')}}</a>.
                    </p>
                    <span class="bold">"Account"</span><br>
                    Means a unique Account created for You to access Our Service or parts of Our Service.
                    </p>
                    <span class="bold">"Country"</span><br>
                    Also referred to as "the Country" refers to the current country of residence of the Company.
                    </p>
                    <span class="bold">"Terms Of Service"</span><br>
                    Also referred as "Terms" mean these Terms Of Service. The Terms Of Service form the entire agreement between You and the Company
                    regarding the use of the Service.
                    </p>
                    <span class="bold">"Third Party Social Media Service"</span><br>
                    Refers to any services or content including data, information or products provided by a third party that may be displayed,
                    included or made available by the Service.
                    </p>
                    <span class="bold">"You"</span><br>
                    Means the individual accessing or using the Service, the Company or other legal entity on behalf of which such individual is
                    accessing or using the Service as applicable.
                    </p>
        </div>
    </div>
    <button onclick="location.href='#table-of-contents'" class="btn-top">&#10146;</button>
</x-page-layout>