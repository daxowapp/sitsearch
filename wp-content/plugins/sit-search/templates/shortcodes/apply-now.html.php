<?php
// UTM tracking is handled site-wide by assets/js/utm-tracker.js
?>


<!-- ADD THIS CSS AND LINK TO YOUR HEAD SECTION -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
<style>
/* Phone input styling - Updated */
.phone-input-container {
    position: relative;
    width: 100%;
}

.phone-input-container .iti {
    width: 100% !important;
    display: block;
}

.phone-input-container .iti__flag-container {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    padding: 0;
    background: transparent;
    border-right: 1px solid #ddd;
    border-radius: 8px 0 0 8px;
}

.phone-input-container .iti__selected-flag {
    padding: 12px 8px 12px 12px;
    display: flex;
    align-items: center;
    height: 100%;
    box-sizing: border-box;
}

.phone-input-container input[type="tel"] {
    width: 100% !important;
    padding: 12px 16px 12px 95px !important;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    height: auto;
    line-height: normal;
    box-sizing: border-box;
}

.phone-input-container input[type="tel"]:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Fix for the dropdown */
.phone-input-container .iti__country-list {
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
}

/* Fix for flag positioning */
.phone-input-container .iti__flag {
    margin-right: 8px;
}

/* Fix for arrow */
.phone-input-container .iti__arrow {
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 4px solid #666;
    margin-left: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .phone-input-container input[type="tel"] {
        padding-left: 85px !important;
    }

    .phone-input-container .iti__selected-flag {
        padding-left: 8px;
        padding-right: 6px;
    }
}
</style>

<div class="apply-container">
  <div class="apply-header">
    <h1 class="apply-title">Begin Your Academic Journey</h1>
    <p class="apply-subtitle">Complete your application form below to take the first step toward your educational goals at our institution.</p>
  </div>

  <div class="apply-layout">
    <!-- Program Summary Card -->
    <div class="apply-program-summary">
      <div class="apply-program-image">
        <img src="<?= $program['image_url'] ?>" alt="<?= $program['title'] ?>">
        <?php if(isset($program['discount']) && $program['discount']): ?>
        <div class="apply-program-discount"><?= $program['discount'] ?> Discount</div>
        <?php endif; ?>
      </div>

      <div class="apply-program-content">
        <div class="apply-program-type"><?= $program['degree_type'] ?? 'Bachelor Program' ?></div>
        <h2 class="apply-program-name"><?= $program['title'] ?></h2>

        <div class="apply-program-university">
          <div class="apply-university-logo">
            <!-- <img src="<?= $program['university_logo'] ?? SIT_SEARCH_ASSETS . 'images/university-logo.png' ?>" alt="University Logo"> -->
          </div>
          <div class="apply-university-name"><?= $program['uni_title'] ?></div>
        </div>

        <div class="apply-program-details">
          <div class="apply-detail-item">
            <span class="apply-detail-label">Duration</span>
            <span class="apply-detail-value"><?= $program['duration'] ?></span>
          </div>
          <div class="apply-detail-item">
            <span class="apply-detail-label">Study Mode</span>
            <span class="apply-detail-value"><?= $program['study_mode'] ?? 'Full-time' ?></span>
          </div>
          <div class="apply-detail-item">
            <span class="apply-detail-label">Start Date</span>
            <span class="apply-detail-value"><?= $program['start_date'] ?? 'September 2025' ?></span>
          </div>
          <div class="apply-detail-item">
            <span class="apply-detail-label">Students</span>
            <span class="apply-detail-value"><?= isset($program['students']) ? $program['students'] : 'N/A' ?></span>
          </div>
        </div>

        <div class="apply-program-cta">
          <div class="apply-program-price">
            <span class="apply-price-label">Annual course fee</span>
            <span class="apply-price-value"><?= $program['fee'] ?> <?= $program['Tuition_Currency'] ?></span>
          </div>
          <a href="<?= $program['link'] ?>" class="apply-program-link">
            Program Details
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </a>

        </div>
      </div>
    </div>

    <!-- Application Form -->
    <div class="apply-form-container">
      <div class="apply-form-header">
        <h2 class="apply-form-title">Complete Your Application</h2>
        <p class="apply-form-description">Fill in the details below to apply for this program</p>
      </div>

      <form class="apply-form-body" id="apply-form-body" enctype="multipart/form-data" action="?" method="post">
        <input type="hidden" name="pro_id" value="<?= $program['pro_id'] ?>">
        <input type="hidden" name="uni_id" value="<?= $program['uni_id'] ?>">
        <input type="hidden" name="degree_id" value="<?= $program['degree_id'] ?>">

        <!-- UTM Tracking (Studyportals + other sources) -->
        <input type="hidden" name="utm_source" id="utm_source" value="">
        <input type="hidden" name="utm_medium" id="utm_medium" value="">
        <input type="hidden" name="utm_campaign" id="utm_campaign" value="">
        <input type="hidden" name="utm_content" id="utm_content" value="">
        <input type="hidden" name="utm_term" id="utm_term" value="">
        <input type="hidden" name="landing_page" id="landing_page" value="">
        <input type="hidden" name="referrer" id="referrer" value="">


        <!-- Personal Information Section -->
        <div class="apply-form-section">
          <h3 class="apply-section-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Personal Information
          </h3>

          <div class="apply-form-row">
            <div class="apply-form-group">
              <label class="apply-form-label">First Name <span class="apply-form-required">*</span></label>
              <input type="text" name="first_name" class="apply-form-control" required placeholder="Enter your first name">
            </div>

            <div class="apply-form-group">
              <label class="apply-form-label">Last Name <span class="apply-form-required">*</span></label>
              <input type="text" name="last_name" class="apply-form-control" required placeholder="Enter your last name">
            </div>
          </div>

          <div class="apply-form-row">
            <div class="apply-form-group">
              <label class="apply-form-label">Email Address <span class="apply-form-required">*</span></label>
              <input type="email" name="email" class="apply-form-control" required placeholder="Enter your email address">
            </div>

            <div class="apply-form-group">
              <label class="apply-form-label">Phone Number <span class="apply-form-required">*</span></label>
              <div class="phone-input-container">
                <input type="tel" name="phone" id="phone" class="apply-form-control" required placeholder="Enter your phone number">
              </div>
            </div>
          </div>
        </div>

        <!-- Citizenship Information -->
        <div class="apply-form-section">
          <h3 class="apply-section-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Citizenship Information
          </h3>

          <div class="apply-form-row">
            <div class="apply-form-group">
              <label class="apply-form-label">Nationality <span class="apply-form-required">*</span></label>
              <select class="apply-form-select" name="country" id="nationality-select" required>
                <option value="">Please select</option>
              </select>
            </div>

            <div class="apply-form-group">
              <label class="apply-form-label">Country of Residence</label>
              <select class="apply-form-select" name="residence_country" id="residence-select">
                <option value="">Please select</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Documents Section -->
        <div class="apply-form-section">
          <h3 class="apply-section-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Required Documents
          </h3>

          <div class="apply-form-row">
            <div class="apply-form-group">
              <label class="apply-form-label">Passport <span class="apply-form-required">*</span></label>
              <div class="apply-file-container">
                <input type="file" name="passport" class="apply-file-input" id="passport-input" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt">
                <div class="apply-file-label" id="passport-label">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <span class="apply-file-text">Select passport file</span>
                </div>
              </div>
              <div class="apply-file-help">Upload your passport (PDF, JPEG, PNG, DOC - Max 10MB)</div>
              <div class="file-name-display" id="passport-filename"></div>
            </div>

            <div class="apply-form-group">
              <label class="apply-form-label">Academic Transcript <span class="apply-form-required">*</span></label>
              <div class="apply-file-container">
                <input type="file" name="transcript" class="apply-file-input" id="transcript-input" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt">
                <div class="apply-file-label" id="transcript-label">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <span class="apply-file-text">Select transcript file</span>
                </div>
              </div>
              <div class="apply-file-help">Upload your academic transcript (PDF, JPEG, PNG, DOC - Max 10MB)</div>
              <div class="file-name-display" id="transcript-filename"></div>
            </div>


          </div>
        </div>

        <!-- KvKK Consent Section -->
        <div class="apply-form-section kvkk-consent-section">
          <div class="kvkk-card">
            <div class="kvkk-card-header">
              <div class="kvkk-shield-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div class="kvkk-header-text">
                <h3 class="kvkk-title">Data Protection Consent</h3>
                <span class="kvkk-subtitle">KvKK Aydınlatma Metni</span>
              </div>
              <span class="kvkk-required-badge">Required</span>
            </div>

            <div class="kvkk-info-panel">
              <div class="kvkk-info-text-en">
                <p>Your personal data (name, email, phone number, nationality, documents) will be processed for evaluating your application and providing educational consultancy services, pursuant to Law No. 6698 (KvKK).</p>
              </div>
              <div class="kvkk-info-text-tr">
                <p>6698 sayılı KvKK kapsamında kişisel verileriniz, başvurunuzun değerlendirilmesi ve eğitim danışmanlığı hizmetlerinin sağlanması amacıyla işlenecektir.</p>
              </div>
            </div>

            <div class="kvkk-consent-items">
              <label class="kvkk-consent-item kvkk-consent-item--required" for="kvkk-consent">
                <div class="kvkk-toggle-wrap">
                  <input type="checkbox" name="kvkk_consent" id="kvkk-consent" required value="1">
                  <span class="kvkk-toggle"></span>
                </div>
                <div class="kvkk-consent-content">
                  <span class="kvkk-consent-text">
                    I have read and accept the
                    <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" class="kvkk-link">Privacy Policy & KvKK Clarification Text</a>
                    and consent to the processing of my personal data.
                  </span>
                  <span class="kvkk-consent-tag kvkk-consent-tag--mandatory">Mandatory</span>
                </div>
              </label>

              <label class="kvkk-consent-item" for="marketing-consent">
                <div class="kvkk-toggle-wrap">
                  <input type="checkbox" name="marketing_consent" id="marketing-consent" value="1">
                  <span class="kvkk-toggle"></span>
                </div>
                <div class="kvkk-consent-content">
                  <span class="kvkk-consent-text">
                    I consent to receiving promotional communications (email, SMS, phone) about educational programs and services.
                  </span>
                  <span class="kvkk-consent-tag kvkk-consent-tag--optional">Optional</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        <button type="submit" class="apply-submit-btn" id="pay-button" disabled>
          <span class="apply-btn-text">Submit Application</span>
          <span class="apply-btn-spinner" style="display:none;">
            <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.3"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></path></svg>
            Submitting...
          </span>
        </button>

        <!-- Submission overlay to prevent double-click -->
        <div class="apply-submit-overlay" id="submit-overlay" style="display:none;">
          <div class="apply-submit-overlay-content">
            <svg width="48" height="48" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#e20a17" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.2"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></path></svg>
            <p>Submitting your application...</p>
            <small>Please wait, uploading your documents</small>
          </div>
        </div>
      </form>

      <div class="apply-form-footer">
        <p class="apply-privacy-text">
          By submitting this form, you agree to our <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" class="apply-privacy-link">Privacy Policy</a> and <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" class="apply-privacy-link">Terms of Service</a>.
        </p>
      </div>
    </div>
  </div>

  <!-- Support Section -->
  <div class="apply-support-section">
    <div class="apply-support-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
    </div>
    <div class="apply-support-content">
      <h4 class="apply-support-heading">Need help with your application?</h4>
      <p class="apply-support-description">Our advisors are available Monday to Friday, 9 AM to 5 PM. Contact us at support@studyinturkiye.com or call +90 545 308 1000.</p>
    </div>
  </div>

  <!-- Legal Disclaimer -->
  <div class="sit-legal-disclaimer">
    <div class="sit-legal-disclaimer-inner">
      <span class="sit-legal-disclaimer-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
      </span>
      <p class="sit-legal-disclaimer-text">
        <strong>OFFICIAL LEGAL DISCLAIMER:</strong> Studyinturkiye.com is a private educational consultancy operated by SIT Consultancy LLC. It is not affiliated with, endorsed by, or part of the Turkish Council of Higher Education (YÖK) or any government authority.
      </p>
    </div>
  </div>
</div>

<!-- ADD THESE SCRIPTS BEFORE YOUR CLOSING BODY TAG -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load countries from REST Countries API
    fetch('https://restcountries.com/v3.1/all?fields=name')
        .then(response => response.json())
        .then(countries => {
            const sortedCountries = countries
                .map(country => country.name.common)
                .sort()
                .filter(country => country !== 'Antarctica'); // Remove Antarctica

            const nationalitySelect = document.getElementById('nationality-select');
            const residenceSelect = document.getElementById('residence-select');

            sortedCountries.forEach(country => {
                const option1 = new Option(country, country);
                const option2 = new Option(country, country);
                nationalitySelect.add(option1);
                residenceSelect.add(option2);
            });
        })
        .catch(error => {
            console.log('Could not load countries from API, using fallback');
            // Fallback country list
            const fallbackCountries = [
                'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan',
                'Bahrain', 'Bangladesh', 'Belarus', 'Belgium', 'Brazil', 'Bulgaria', 'Cambodia', 'Canada', 'Chile',
                'China', 'Colombia', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Egypt', 'Estonia', 'Finland',
                'France', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Hungary', 'Iceland', 'India', 'Indonesia',
                'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kuwait',
                'Latvia', 'Lebanon', 'Lithuania', 'Luxembourg', 'Malaysia', 'Mexico', 'Morocco', 'Netherlands',
                'New Zealand', 'Nigeria', 'Norway', 'Pakistan', 'Philippines', 'Poland', 'Portugal', 'Qatar',
                'Romania', 'Russia', 'Saudi Arabia', 'Singapore', 'Slovakia', 'Slovenia', 'South Africa',
                'South Korea', 'Spain', 'Sri Lanka', 'Sweden', 'Switzerland', 'Thailand', 'Turkey',
                'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Vietnam'
            ];

            const nationalitySelect = document.getElementById('nationality-select');
            const residenceSelect = document.getElementById('residence-select');

            fallbackCountries.forEach(country => {
                const option1 = new Option(country, country);
                const option2 = new Option(country, country);
                nationalitySelect.add(option1);
                residenceSelect.add(option2);
            });
        });

    // Initialize phone input with country detection
    const phoneInput = document.querySelector("#phone");

    if (phoneInput) {
        // Get user's country from IP
        fetch('https://ipapi.co/json/')
            .then(response => response.json())
            .then(data => {
                const countryCode = data.country_code ? data.country_code.toLowerCase() : 'tr';

                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: countryCode,
                    preferredCountries: ['tr', 'us', 'gb', 'de', 'fr'],
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                });

                phoneInput.addEventListener('blur', function() {
                    const fullNumber = iti.getNumber();
                    phoneInput.value = fullNumber;
                });

                phoneInput.addEventListener('countrychange', function() {
                    const fullNumber = iti.getNumber();
                    if (phoneInput.value) {
                        phoneInput.value = fullNumber;
                    }
                });
            })
            .catch(error => {
                console.log('Could not detect country, using default');
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: 'tr',
                    preferredCountries: ['tr', 'us', 'gb', 'de', 'fr'],
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                });
            });
    }

    // File upload feedback functionality
    function setupFileInput(inputId, labelId, filenameId) {
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);
        const filename = document.getElementById(filenameId);

        if (input && label && filename) {
            const textSpan = label.querySelector('.apply-file-text');

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    label.classList.add('file-selected');
                    textSpan.textContent = '✓ File selected';

                    filename.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                    filename.style.display = 'block';

                    const svg = label.querySelector('svg');
                    if (svg) {
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
                    }
                } else {
                    label.classList.remove('file-selected');
                    textSpan.textContent = inputId.includes('passport') ? 'Select passport file' : 'Select transcript file';
                    filename.textContent = '';
                    filename.style.display = 'none';

                    const svg = label.querySelector('svg');
                    if (svg) {
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />';
                    }
                }
            });
        }
    }

    // Setup both file inputs
    setupFileInput('passport-input', 'passport-label', 'passport-filename');
    setupFileInput('transcript-input', 'transcript-label', 'transcript-filename');

    // KvKK consent toggle: enable/disable submit button
    var kvkkCheckbox = document.getElementById('kvkk-consent');
    var payBtn = document.getElementById('pay-button');

    if (kvkkCheckbox && payBtn) {
      kvkkCheckbox.addEventListener('change', function() {
        payBtn.disabled = !this.checked;
      });
    }

    // Prevent double-submit: show spinner + overlay
    var applyForm = document.getElementById('apply-form-body');
    var overlay = document.getElementById('submit-overlay');

    if (applyForm && payBtn && overlay) {
      applyForm.addEventListener('submit', function(e) {
        // KvKK consent validation (safety net)
        if (kvkkCheckbox && !kvkkCheckbox.checked) {
          e.preventDefault();
          kvkkCheckbox.closest('.kvkk-card').classList.add('kvkk-error');
          kvkkCheckbox.focus();
          return false;
        }



        // Show spinner in button
        var btnText = payBtn.querySelector('.apply-btn-text');
        var btnSpinner = payBtn.querySelector('.apply-btn-spinner');
        if (btnText) btnText.style.display = 'none';
        if (btnSpinner) btnSpinner.style.display = 'inline-flex';

        // Disable button
        payBtn.disabled = true;
        payBtn.style.opacity = '0.7';
        payBtn.style.cursor = 'not-allowed';

        // Show overlay
        overlay.style.display = 'flex';
      });
    }
});
</script>

<!-- UTM Capture + Persist + Fill Hidden Inputs -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  function getParam(name) {
    const url = new URL(window.location.href);
    return (url.searchParams.get(name) || "").trim();
  }

  const utmKeys = ["utm_source","utm_medium","utm_campaign","utm_content","utm_term"];
  const now = Date.now();
  const expiryDays = 30;
  const expiryMs = expiryDays * 24 * 60 * 60 * 1000;

  function readStore() {
    try { return JSON.parse(localStorage.getItem("lead_tracking") || "{}"); }
    catch (e) { return {}; }
  }
  function writeStore(data) {
    localStorage.setItem("lead_tracking", JSON.stringify(data));
  }

  // clear expired
  const savedBefore = readStore();
  if (savedBefore.saved_at && (now - savedBefore.saved_at) > expiryMs) {
    localStorage.removeItem("lead_tracking");
  }

  const current = {};
  let hasUtm = false;
  utmKeys.forEach(k => {
    const v = getParam(k);
    if (v) { current[k] = v; hasUtm = true; }
  });

  const landingPage = window.location.href.split('#')[0];
  const referrer = document.referrer || "";

  // overwrite attribution ONLY when UTMs exist in URL
  if (hasUtm) {
    const saved = readStore();
    writeStore({
      ...saved,
      ...current,
      landing_page: landingPage,
      referrer: referrer,
      saved_at: now
    });
  }

  function setValue(id, value) {
    const el = document.getElementById(id);
    if (el && !el.value) el.value = value || "";
  }

  const final = readStore();

  setValue("utm_source", final.utm_source || "");
  setValue("utm_medium", final.utm_medium || "");
  setValue("utm_campaign", final.utm_campaign || "");
  setValue("utm_content", final.utm_content || "");
  setValue("utm_term", final.utm_term || "");
  setValue("landing_page", final.landing_page || landingPage);
  setValue("referrer", final.referrer || referrer);

  // if prog_id exists and utm_content is empty, set it
  const progId = getParam("prog_id");
  const utmContentEl = document.getElementById("utm_content");
  if (progId && utmContentEl && !utmContentEl.value) {
    utmContentEl.value = "program_" + progId;
  }
});
</script>



