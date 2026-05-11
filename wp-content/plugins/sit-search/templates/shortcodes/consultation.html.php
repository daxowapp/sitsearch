<div class="applynow-con consultation-form">

    <div class="row row-main">

        <!-- Left Section: Apply Now Form -->

        <div class="col-md-12 text-center">

            <h2 class="apply-title">Consultation</h2>

        </div>

        <div class="col-md-12">

            <form enctype="multipart/form-data" action="?" method="post">

                <input type="hidden" name="uni_id" value="<?= $uni_details['uni_id'] ?>">
                <!-- UTM Tracking (auto-filled by sit-utm-tracker.js) -->
                <input type="hidden" name="utm_source" value="">
                <input type="hidden" name="utm_medium" value="">
                <input type="hidden" name="utm_campaign" value="">
                <input type="hidden" name="utm_content" value="">
                <input type="hidden" name="utm_term" value="">
                <input type="hidden" name="landing_page" value="">
                <input type="hidden" name="referrer" value="">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">First Name <span class="text-danger">*</span></label>

                        <input type="text" name="first_name" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Last Name <span class="text-danger">*</span></label>

                        <input type="text" name="last_name" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-12">

                        <label class="form-label">Email Address <span class="text-danger">*</span></label>

                        <input type="email" name="email" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-12">

                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>

                        <input type="tel" name="phone" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-12">

                        <label class="form-label">Preferred study Level</label>

                        <select class="form-select custom-input" name="study_level">
                            <option>Please select</option>
                            <option value="Associate's">Associate's</option>
                            <option value="Bachelor's">Bachelor's</option>
                            <option value="Master's">Master's</option>
                            <option value="Phd">Phd</option>

                        </select>

                    </div>
                    <div class="col-md-12">

                        <label class="form-label">Preferred study Year</label>

                        <select class="form-select custom-input" name="study_year">

                            <option>Please select</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>

                        </select>

                    </div>

                </div>

                <!-- KvKK Consent Section -->
                <div class="kvkk-consent-section" style="margin-top: 20px;">
                  <div class="kvkk-consent-box">
                    <div class="kvkk-info-text">
                      <p>Pursuant to the Turkish Personal Data Protection Law No. 6698 (KvKK), your personal data will be processed for the purpose of evaluating your consultation request and providing educational consultancy services.</p>
                      <p>6698 sayılı Kişisel Verilerin Korunması Kanunu (KvKK) kapsamında, kişisel verileriniz danışmanlık talebinizin değerlendirilmesi ve eğitim danışmanlığı hizmetlerinin sağlanması amacıyla işlenecektir.</p>
                    </div>

                    <label class="kvkk-checkbox-label" for="kvkk-consent-consultation">
                      <input type="checkbox" name="kvkk_consent" id="kvkk-consent-consultation" required value="1">
                      <span class="kvkk-checkmark"></span>
                      <span class="kvkk-label-text">
                        I have read and accept the
                        <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" class="kvkk-link">KvKK Clarification Text</a>
                        and consent to the processing of my personal data as described above.
                        <span class="text-danger">*</span>
                      </span>
                    </label>

                    <label class="kvkk-checkbox-label" for="marketing-consent-consultation">
                      <input type="checkbox" name="marketing_consent" id="marketing-consent-consultation" value="1">
                      <span class="kvkk-checkmark"></span>
                      <span class="kvkk-label-text">
                        I consent to receiving promotional communications (email, SMS, phone) about educational programs and services.
                      </span>
                    </label>
                  </div>
                </div>

                <button type="submit" class="btn btn-danger apply-btn" id="consultation-submit-btn" disabled>Apply Now</button>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var kvkkCheckbox = document.getElementById('kvkk-consent-consultation');
                    var submitBtn = document.getElementById('consultation-submit-btn');
                    if (kvkkCheckbox && submitBtn) {
                        kvkkCheckbox.addEventListener('change', function() {
                            submitBtn.disabled = !this.checked;
                        });
                    }
                });
                </script>

                <div class="apply-form-footer" style="margin-top: 16px;">
                  <p class="apply-privacy-text" style="font-size: 12px; color: #6c757d; text-align: center;">
                    By submitting this form, you agree to our <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" style="color: #e20a17;">Privacy Policy</a> and <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" style="color: #e20a17;">Terms of Service</a>.
                  </p>
                </div>

            </form>

        </div>



    </div>

</div>