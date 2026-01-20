<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;
use SIT\Search\Services\Zoho;

class ApplyNow
{
    public function __invoke()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
            $last_name  = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
            $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone      = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $nationality = isset($_POST['country']) ? trim($_POST['country']) : '';
            $residence_country = isset($_POST['residence_country']) ? trim($_POST['residence_country']) : '';
            $prog_id    = isset($_POST['pro_id']) ? trim($_POST['pro_id']) : '';
            $uni_id     = isset($_POST['uni_id']) ? trim($_POST['uni_id']) : '';
            $degree_id  = isset($_POST['degree_id']) ? trim($_POST['degree_id']) : '';

            // --- UTM fields coming from your hidden inputs (apply-now.html.php) ---
            $utm_source   = isset($_POST['utm_source']) ? trim($_POST['utm_source']) : '';
            $utm_medium   = isset($_POST['utm_medium']) ? trim($_POST['utm_medium']) : '';
            $utm_campaign = isset($_POST['utm_campaign']) ? trim($_POST['utm_campaign']) : '';
            $utm_content  = isset($_POST['utm_content']) ? trim($_POST['utm_content']) : '';
            $utm_term     = isset($_POST['utm_term']) ? trim($_POST['utm_term']) : '';
            
            // Optional tracking fields (only if you created them in Zoho; otherwise ignore)
            $landing_page = isset($_POST['landing_page']) ? trim($_POST['landing_page']) : '';
            $referrer     = isset($_POST['referrer']) ? trim($_POST['referrer']) : '';

            // Clean phone number to handle international format
            if (!empty($phone)) {
                $phone = preg_replace('/[^\+\d]/', '', $phone); // Keep only + and digits
            }

            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
                die("All required fields must be filled!");
            }

            // (Optional) server-side debug to confirm UTMs reach PHP
            // Check your PHP error log after submission
            error_log("ApplyNow POST UTM DEBUG: " . json_encode([
                'utm_source' => $utm_source,
                'utm_medium' => $utm_medium,
                'utm_campaign' => $utm_campaign,
                'utm_content' => $utm_content,
                'utm_term' => $utm_term,
                'landing_page' => $landing_page,
                'referrer' => $referrer,
                'email' => $email
            ]));

            $lead_data = array(
                "data" => array(
                    array(
                        "First_Name" => $first_name,
                        "Last_Name"  => $last_name,
                        "Email"      => $email,
                        "Phone"      => $phone,
                        "Agent_Country" => $nationality,
                        "Country"    => $residence_country,
                        "Lead_Source" => "Website",

                        // --- Zoho UTM Fields (API Names confirmed by you) ---
                        "UTM_Source"   => $utm_source,
                        "UTM_Medium"   => $utm_medium,
                        "UTM_Campaign" => $utm_campaign,
                        "UTM_Content"  => $utm_content,
                        "UTM_Term"     => $utm_term,

                        // NOTE:
                        // Only add these if you have matching Zoho fields with these API names.
                        // If you do NOT have fields, remove them to avoid any CRM rejection.
                        // "Landing_Page" => $landing_page,
                        // "Referrer_URL" => $referrer,

                        "Tag" => array(
                            array(
                                "name" => "Website Application"
                            )
                        ),
                        "Program" => array(
                            'id' => $prog_id
                        ),
                        "University" => array(
                            'id' => $uni_id
                        ),
                        "Degree" => array(
                            'id' => $degree_id
                        ),
                        "Layout" => array(
                            "id" => "6421426000004346065"
                        )
                    )
                ),
                "trigger" => array("workflow")
            );

            $response = (new Zoho())->request('Leads', 'POST', $lead_data);

            // Debug if Zoho rejects or trims fields
            error_log("ApplyNow Zoho Response: " . json_encode($response));

            if (isset($response['data'][0]['details']['id'])) {
                $lead_id = $response['data'][0]['details']['id'];

                // Handle file uploads with improved error handling
                $this->handleFileUploads($lead_id);
                ?>
                <div class="sit-thank-you-container">
                    <div class="sit-thank-you-card">
                        <div class="sit-thank-you-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sit-thank-you-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>

                        <h2 class="sit-thank-you-title">Thank You</h2>
                        <h3 class="sit-thank-you-subtitle">Application Received!</h3>

                        <div class="sit-thank-you-message">
                            Thank you for reaching out! We have received your message and will get back to you as soon as possible. 
                            If your inquiry is urgent, please feel free to contact us directly. We appreciate your interest and look forward to assisting you!
                        </div>
                        
                        <a href="/" class="sit-thank-you-home-btn">Return to Home</a>
                    </div>
                </div>
                <?php
            } else {
                echo "Error creating lead!";
                error_log("Lead creation failed: " . json_encode($response));
            }
        }

        $prog_id = isset($_GET['prog_id']) && $_GET['prog_id'] != 0 ? intval($_GET['prog_id']) : '';
        ob_start();

        if ($prog_id) {
            $program = get_post($prog_id);
            $uni_id = get_post_meta($prog_id, 'zh_university', true);
            $university = get_post($uni_id);
            $degree_terms = get_the_terms($prog_id, 'sit-degree');
            $degree_id = !empty($degree_terms) && !is_wp_error($degree_terms) ? $degree_terms[0]->term_id : '';
            $zoho_degree_id = !empty($degree_id) ? get_term_meta($degree_id, 'zoho_degree_id', true) : '';

            // Get country and city terms safely
            $country_terms = get_the_terms($prog_id, 'sit-country');
            $pro_country = !empty($country_terms) && !is_wp_error($country_terms) ? $country_terms[0]->name : '';

            $uni_country_terms = get_the_terms($uni_id, 'sit-country');
            $uni_country = !empty($uni_country_terms) && !is_wp_error($uni_country_terms) ? $uni_country_terms[0]->name : '';

            $city_terms = get_the_terms($uni_id, 'sit-city');
            $uni_city = !empty($city_terms) && !is_wp_error($city_terms) ? $city_terms[0]->name : '';

            $programs = [
                'pro_id' => get_post_meta($prog_id, 'zoho_product_id', true),
                'uni_id' => get_post_meta($uni_id, 'zoho_account_id', true),
                'type' => get_post_meta($uni_id, 'Sector', true),
                'title' => $program->post_title,
                'link' => $program->guid,
                'fee' => get_post_meta($prog_id, 'Official_Tuition', true),
                'Tuition_Currency' => get_post_meta($prog_id, 'Tuition_Currency', true),
                'Service_fee' => get_post_meta($prog_id, 'Service_fee', true),
                'Application_Fee' => get_post_meta($prog_id, 'Application_Fee', true),
                'duration' => get_post_meta($prog_id, 'Study_Years', true),
                'pro_country' => $pro_country,
                'degree_id' => $zoho_degree_id,
                'country' => $uni_country,
                'city' => $uni_city,
                'description' => get_post_meta($prog_id, 'Description', true),
                'uni_description' => get_post_meta($uni_id, 'Description', true),
                'ranking' => get_post_meta($uni_id, 'QS_Rank', true),
                'uni_title' => $university->post_title,
                'uni_link' => $university->guid,
                'image_url' => !empty(get_post_meta($uni_id, 'uni_image', true)) ?
                    esc_url(get_post_meta($uni_id, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
                'Year_Founded' => get_post_meta($uni_id, 'Year_Founded', true),
                'total_students' => get_post_meta($uni_id, 'Number_Of_Students', true),
            ];

            Template::render('shortcodes/apply-now', ['program' => $programs]);
        }

        return ob_get_clean();
    }

    /**
     * Handle file uploads with proper validation and error handling
     */
    private function handleFileUploads($lead_id)
    {
        $this->uploadFileToZoho('passport', $lead_id, 'passport');
        $this->uploadFileToZoho('transcript', $lead_id, 'transcript');
    }

    /**
     * Upload a single file to Zoho with proper validation
     */
    private function uploadFileToZoho($file_input_name, $lead_id, $file_prefix)
    {
        if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_NO_FILE) {
                error_log("File upload error for '$file_input_name': " . $_FILES[$file_input_name]['error']);
            }
            return false;
        }

        $original_name = $_FILES[$file_input_name]['name'];
        $file_path = $_FILES[$file_input_name]['tmp_name'];
        $file_type = $_FILES[$file_input_name]['type'];
        $file_size = $_FILES[$file_input_name]['size'];

        if ($file_size > 10 * 1024 * 1024) {
            echo "<div class='error-message'>Error: File '$original_name' is too large (maximum 10MB allowed)</div>";
            return false;
        }

        if ($file_size == 0) {
            echo "<div class='error-message'>Error: File '$original_name' is empty</div>";
            return false;
        }

        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
        $allowed_mime_types = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];

        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<div class='error-message'>Error: File type not allowed for '$original_name'. Allowed types: " . implode(', ', $allowed_extensions) . "</div>";
            return false;
        }

        if (!in_array($file_type, $allowed_mime_types)) {
            error_log("MIME type mismatch for '$original_name': got '$file_type', expected one of: " . implode(', ', $allowed_mime_types));
        }

        $safe_filename = $file_prefix . '_' . date('Y-m-d_H-i-s') . '.' . $file_extension;

        if (is_uploaded_file($file_path)) {
            $file_data = [
                'file_name' => $safe_filename,
                'file_path' => $file_path,
                'file_type' => $file_type
            ];

            try {
                $upload_response = (new Zoho())->request_att("Leads/$lead_id/Attachments", 'POST', $file_data);

                if (isset($upload_response['data']) && !empty($upload_response['data'])) {
                    return true;
                } else {
                    echo "<div class='error-message'>Error uploading file: $original_name</div>";
                    error_log("Zoho file upload failed for '$original_name': " . json_encode($upload_response));
                    return false;
                }
            } catch (\Exception $e) {
                echo "<div class='error-message'>Error uploading file: $original_name - " . $e->getMessage() . "</div>";
                error_log("Exception during file upload for '$original_name': " . $e->getMessage());
                return false;
            }
        } else {
            echo "<div class='error-message'>Error: File upload validation failed for '$original_name'</div>";
            return false;
        }
    }
}
