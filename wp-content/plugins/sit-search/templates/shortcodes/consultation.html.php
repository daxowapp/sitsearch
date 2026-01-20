<div class="applynow-con consultation-form">

    <div class="row row-main">

        <!-- Left Section: Apply Now Form -->

        <div class="col-md-12 text-center">

            <h2 class="apply-title">Consultation</h2>

        </div>

        <div class="col-md-12">

            <form enctype="multipart/form-data" action="?" method="post">

                <input type="hidden" name="uni_id" value="<?= $uni_details['uni_id'] ?>">

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



                <button type="submit" class="btn btn-danger apply-btn">Apply Now</button>

            </form>

        </div>



    </div>

</div>