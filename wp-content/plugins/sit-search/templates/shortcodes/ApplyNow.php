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
            $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $nationality = isset($_POST['country']) ? trim($_POST['country']) : '';
            $residence_country = isset($_POST['residence_country']) ? trim($_POST['residence_country']) : '';
            $prog_id = isset($_POST['pro_id']) ? trim($_POST['pro_id']) : '';
            $uni_id = isset($_POST['uni_id']) ? trim($_POST['uni_id']) : '';
            $degree_id = isset($_POST['degree_id']) ? trim($_POST['degree_id']) : '';
            
            // Clean phone number to handle international format
            if (!empty($phone)) {
                $phone = preg_replace('/[^\+\d]/', '', $phone); // Keep only + and digits
            }
            
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
                die("All required fields must be filled!");
            }

            $lead_data = array(
                "data" => array(
                    array(
                        "First_Name" => $first_name,
                        "Last_Name" => $last_name,
                        "Email" => $email,
                        "Phone" => $phone,
                        "Agent_Country" => $nationality,
                        "Country" => $residence_country,
                        "Lead_Source" => "Website",
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
            
            if (isset($response['data'][0]['details']['id'])) {
                $lead_id = $response['data'][0]['details']['id'];
                
                // Handle file uploads with improved error handling
                $this->handleFileUploads($lead_id);
                
                ?>
                <div class='thank-you-section'>
                    <div class='arrow arrow-left'>
                        <img src='https://search.studyinturkiye.com/wp-content/uploads/2025/05/lined-and-blured-background-2-2.png' alt='Arrow Left'>
                    </div>

                    <div class='arrow arrow-right'>
                        <img src='https://search.studyinturkiye.com/wp-content/uploads/2025/05/lined-and-blured-background-2-1-1.png' alt='Arrow Right'>
                    </div>

                    <div class="thankyou-content">
                        <div class='thank-you-text'>THANK YOU</div>
                        <div class='emoji-books'><img src="https://search.studyinturkiye.com/wp-content/uploads/2025/05/Design.png"></div>
                    </div>
                    
                    <div class='thank-you-message'>
                        Thank you for reaching out! We have received your message and will get back to you as soon as possible. If your inquiry is urgent, please feel free to contact us directly. We appreciate your interest and look forward to assisting you!
                    </div>
                </div>
                <?php
            } else {
                echo "Error creating lead!";
                // Debug: Log the response for troubleshooting
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
        // Upload passport file
        $this->uploadFileToZoho('passport', $lead_id, 'passport');
        
        // Upload transcript file
        $this->uploadFileToZoho('transcript', $lead_id, 'transcript');
    }

    /**
     * Upload a single file to Zoho with proper validation
     */
    private function uploadFileToZoho($file_input_name, $lead_id, $file_prefix)
    {
        if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
            // No file uploaded or upload error - this is not necessarily an error for optional files
            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_NO_FILE) {
                error_log("File upload error for '$file_input_name': " . $_FILES[$file_input_name]['error']);
            }
            return false;
        }

        $original_name = $_FILES[$file_input_name]['name'];
        $file_path = $_FILES[$file_input_name]['tmp_name'];
        $file_type = $_FILES[$file_input_name]['type'];
        $file_size = $_FILES[$file_input_name]['size'];

        // Validate file size (max 10MB)
        if ($file_size > 10 * 1024 * 1024) {
            echo "<div class='error-message'>Error: File '$original_name' is too large (maximum 10MB allowed)</div>";
            return false;
        }

        // Validate file is not empty
        if ($file_size == 0) {
            echo "<div class='error-message'>Error: File '$original_name' is empty</div>";
            return false;
        }

        // Get file extension
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // Validate file type
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

        // Additional MIME type check (more flexible as some servers return different MIME types)
        if (!in_array($file_type, $allowed_mime_types)) {
            // Log but don't fail - some servers return different MIME types for the same file
            error_log("MIME type mismatch for '$original_name': got '$file_type', expected one of: " . implode(', ', $allowed_mime_types));
        }

        // Create proper filename with original extension
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
                    // Success - you can optionally show success message
                    // echo "<div class='success-message'>Successfully uploaded: $safe_filename</div>";
                    return true;
                } else {
                    echo "<div class='error-message'>Error uploading file: $original_name</div>";
                    // Log the detailed error for debugging
                    error_log("Zoho file upload failed for '$original_name': " . json_encode($upload_response));
                    return false;
                }
            } catch (Exception $e) {
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