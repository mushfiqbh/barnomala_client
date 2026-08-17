@php
    $schoolName = $options['institute.branding.name'] ?? config('app.name', 'Barnomala');
    $footerText = $options['institute.footer.text'] ?? '';
    $address = $options['institute.contact.address'] ?? '';
    $phone = $options['institute.contact.phone'] ?? '01234-567890';
    $email = $options['institute.contact.email'] ?? 'info@barnomala.com';
    $eiin = $options['institute.identity.eiin'] ?? 'N/A';
    $code = $options['institute.identity.code'] ?? 'N/A';
    $logoUrl = $options['institute.branding.logo_json']['url'] ?? asset('images/school-logo.png');
    $visitorCount = \App\Models\Option::get('site.visitor_count', 0);
    $facebook = $options['institute.social.facebook'] ?? '';
    $youtube = $options['institute.social.youtube'] ?? '';
    $whatsapp = $options['institute.social.whatsapp'] ?? $phone;
    $instagram = $options['institute.social.instagram'] ?? '';
    $linkedin = $options['institute.social.linkedin'] ?? '';
    $twitter = $options['institute.social.twitter'] ?? '';

    if( str_starts_with($whatsapp, '01')){
        $whatsapp = '+88' . $whatsapp;
    }
@endphp

<footer class="footer-new">
    <!-- Top Social Bar -->
    <div class="footer-new-top">
        @if($facebook)
            <a href="{{ $facebook }}" target="_blank" title="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
        @endif

        @if($youtube)
            <a href="{{ $youtube }}" target="_blank" title="YouTube">
                <i class="fab fa-youtube"></i>
            </a>
        @endif

        @if($whatsapp)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" title="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
        @endif

        @if($instagram)
            <a href="{{ $instagram }}" target="_blank" title="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
        @endif

        @if($linkedin)
            <a href="{{ $linkedin }}" target="_blank" title="LinkedIn">
                <i class="fab fa-linkedin-in"></i>
            </a>
        @endif

        @if($twitter)
            <a href="{{ $twitter }}" target="_blank" title="Twitter">
                <i class="fab fa-twitter"></i>
            </a>
        @endif
    </div>

    <div class="max-w-[90%] mx-auto my-2">
        <div class="footer-new-main">
            <!-- Left: School Info -->
            <div class="footer-new-left">
                <img src="{{ $logoUrl }}" alt="{{ $schoolName }}">
                <h3>{{ $schoolName }}</h3>
                
                @if($footerText)
                    <p class="font-bn">{{ $footerText }}</p>
                @endif

                <div class="mt-4 space-y-1">
                    @if($phone)
                        <p>📞 Contact: {{ $phone }}</p>
                    @endif
                    @if($email)
                        <p>📧 Email: {{ $email }}</p>
                    @endif
                    @if($address)
                        <p>📍 Address: {{ $address }}</p>
                    @endif
                </div>
            </div>

            <!-- Middle: Quick Links -->
            <div class="footer-new-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/notices">Notice Board</a></li>
                    <li><a href="{{ route('apply.index') }}">Online Admission</a></li>
                    <li><a href="/teachers">Our Teachers</a></li>
                    <li><a href="/gallery">Photo Gallery</a></li>
                    <li><a href="/contact-us">Contact Us</a></li>
                    <li><a href="https://moedu.gov.bd/" target="_blank">Ministry of Education</a></li>
                    <li><a href="https://dshe.gov.bd/" target="_blank">DSHE</a></li>
                    <li><a href="http://www.banbeis.gov.bd/" target="_blank">BANBEIS</a></li>
                </ul>
            </div>

            <!-- Right: Maintained By & Visitors -->
            <div class="footer-new-right">
                <div class="visitor">{{ number_format( floatval($visitorCount) ) }}</div>
                <p>ONLINE VISITOR</p>
                <br/>
                <br/>
                <h4>Maintained By</h4>
                <a href="https://barnomala.com" target="_blank">
                  <img src="{{ asset('images/barnomala-logo-new.png') }}" alt="Barnomala" style="margin: 0 auto 10px;">
                </a>
            </div>
        </div>

        <!-- Bottom Copyright Area -->
        <div class="footer-new-bottom">
            &copy; {{ now()->year }} <b>{{ $schoolName }}</b>. All Rights Reserved.
            <br>
            Website designed & developed by <a href="https://ms3technology.com.bd" target="_blank">MS3 Technology BD</a>
        </div>
    </div>
</footer>

<button id="backToTop" title="Go to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
    // Show/hide back to top button
    window.addEventListener('scroll', function() {
        const btn = document.getElementById("backToTop");
        if (window.pageYOffset > 300) {
            btn.style.display = "flex";
        } else {
            btn.style.display = "none";
        }
    });

    // Smooth scroll to top
    document.getElementById("backToTop").addEventListener('click', function() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
</script>


<style>
    .footer-new {
        background: #fff;
        border-top: 1px solid #ddd;
        position: relative;
    }

    @keyframes waveAnimation {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(100%);
        }
    }

    .footer-new-top {
        background: #0056b3;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px 0;
        gap: 30px;
        flex-wrap: wrap;
    }

    .footer-new-top a {
        color: white !important;
        font-size: 24px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .footer-new-top a:hover {
        transform: scale(1.2) translateY(-5px);
        color: #f8b239 !important;
    }

    .footer-new-main {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 10px 50px;
        gap: 40px;
    }

    /* Left section */
    .footer-new-left {
        max-width: 300px;
    }

    .footer-new-left img {
        max-width: 80px;
        display: block;
        margin-bottom: 10px;
    }

    .footer-new-left h3 {
        margin: 10px 0 5px;
        color: #003366;
        font-size: 18px;
        font-weight: bold;
    }

    .footer-new-left p {
        margin: 5px 0;
        color: #555;
        font-size: 14px;
    }

    /* Quick Links */
    .footer-new-links {
        flex: 1;
        min-width: 200px;
    }

    .footer-new-links h4 {
        margin-bottom: 15px;
        color: #003366;
        border-bottom: 2px solid #0056b3;
        display: inline-block;
        padding-bottom: 3px;
        font-size: 16px;
        font-weight: bold;
    }

    .footer-new-links ul {
        list-style: none;
        padding: 0;
        margin: 0;
        columns: 2;
        column-gap: 20px;
    }

    .footer-new-links ul li {
        margin-bottom: 8px;
        break-inside: avoid;
    }

    .footer-new-links ul li a {
        text-decoration: none;
        color: #333 !important;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .footer-new-links ul li a:hover {
        color: #0056b3 !important;
    }

    /* Right Section */
    .footer-new-right {
        text-align: center;
        max-width: 200px;
    }

    .footer-new-right h4 {
        color: #003366;
        margin-bottom: 15px;
        font-size: 16px;
        font-weight: bold;
    }

    .footer-new-right img {
        max-width: 120px;
        margin-bottom: 15px;
        filter: none;
    }

    .footer-new-right .visitor {
        font-size: 28px;
        font-weight: bold;
        color: #0056b3;
    }

    .footer-new-right p {
        margin: 0;
        font-size: 14px;
        color: #333;
        font-weight: bold;
    }

    /* Bottom */
    .footer-new-bottom {
        text-align: center;
        font-size: 14px;
        border-top: 1px solid #ddd;
        padding: 15px 0;
        color: #666;
    }

    .footer-new-bottom a {
        color: #0056b3 !important;
        font-weight: bold;
        text-decoration: none;
    }

    .footer-new-bottom a:hover {
        text-decoration: underline;
    }

    #backToTop {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        cursor: pointer;
        display: none;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #backToTop i {
        color: #0056b3;
        font-size: 20px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .footer-new-main {
            padding: 10px 30px;
            gap: 30px;
        }

        .footer-new-left, .footer-new-links, .footer-new-right {
            flex: 1;
            min-width: 200px;
        }
    }

    @media (max-width: 768px) {
        .footer-new-main {
            flex-direction: column;
            padding: 10px 15px;
            gap: 15px;
        }

        .footer-new-left, .footer-new-right {
            max-width: 100%;
            text-align: center;
        }

        .footer-new-left img {
            max-width: 60px;
            margin: 0 auto 10px;
        }

        .footer-new-left h3 {
            font-size: 16px;
        }

        .footer-new-left p {
            font-size: 13px;
        }

        .footer-new-links {
            text-align: center;
        }

        .footer-new-links h4 {
            font-size: 15px;
            display: block;
        }

        .footer-new-links ul {
            columns: 1;
            text-align: center;
        }

        .footer-new-links ul li {
            margin-bottom: 6px;
        }

        .footer-new-links ul li a {
            font-size: 13px;
        }

        .footer-new-right {
            margin-top: 10px;
        }

        .footer-new-right img {
            max-width: 100px;
            margin: 0 auto 10px;
        }

        .footer-new-right .visitor {
            font-size: 24px;
        }

        .footer-new-right p {
            font-size: 13px;
        }

        .footer-new-top {
            padding: 15px 10px;
            gap: 20px;
        }

        .footer-new-top a {
            font-size: 20px;
        }

        #backToTop {
            width: 45px;
            height: 45px;
            bottom: 15px;
            right: 15px;
        }

        #backToTop i {
            font-size: 18px;
        }

        .footer-new-bottom {
            font-size: 12px;
            padding: 10px 5px;
        }
    }

    @media (max-width: 480px) {
        .footer-new-main {
            padding: 10px;
            gap: 10px;
        }

        .footer-new-left img {
            max-width: 50px;
            margin-bottom: 8px;
        }

        .footer-new-left h3 {
            font-size: 14px;
            margin: 8px 0 3px;
        }

        .footer-new-left p {
            font-size: 12px;
            margin: 3px 0;
        }

        .footer-new-links {
            margin-top: 10px;
        }

        .footer-new-links h4 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .footer-new-links ul li a {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .footer-new-right {
            margin-top: 10px;
        }

        .footer-new-right img {
            max-width: 90px;
        }

        .footer-new-right .visitor {
            font-size: 20px;
        }

        .footer-new-right p {
            font-size: 12px;
        }

        .footer-new-right h4 {
            font-size: 13px;
            margin-bottom: 10px;
        }

        .footer-new-top {
            padding: 12px 8px;
            gap: 15px;
        }

        .footer-new-top a {
            font-size: 18px;
        }

        .footer-new-bottom {
            font-size: 11px;
            padding: 8px 3px;
        }

        #backToTop {
            width: 40px;
            height: 40px;
            bottom: 10px;
            right: 10px;
        }

        #backToTop i {
            font-size: 16px;
        }
    }
</style>