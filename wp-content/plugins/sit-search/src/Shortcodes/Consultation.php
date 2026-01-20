<?php



namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

use SIT\Search\Services\Zoho;

class Consultation

{

    public function __invoke()

    {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {


            echo "<pre>";print_r($_POST);exit;

            $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';

            $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';

            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

            $uni_id = isset($_POST['uni_id']) ? trim($_POST['uni_id']) : '';

            $study_level = isset($_POST['study_level']) ? trim($_POST['study_level']) : '';

            $study_year = isset($_POST['study_year']) ? trim($_POST['study_year']) : '';



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

                        "Study_year" => $study_year,

                        "Study_level" => $study_level,

                        "Lead_Source" => "Consultation",

                    )

                ),

                "trigger" => array("workflow")

            );



            $response = (new Zoho())->request('Leads', 'POST', $lead_data);

            if (isset($response['data'][0]['details']['id'])) {

                $lead_id = $response['data'][0]['details']['id'];

                if (!empty($_FILES['passport']['name'])) {

                    $file_name = $_FILES['passport']['name'];

                    $file_path = $_FILES['passport']['tmp_name'];

                    $file_type = $_FILES['passport']['type'];



                    if (is_uploaded_file($file_path)) {

                        $file_data = [

                            'file_name' => 'passport.jpg',

                            'file_path' => $file_path,

                            'file_type' => $file_type

                        ];



                        $upload_response = (new Zoho())->request_att("Leads/$lead_id/Attachments", 'POST', $file_data);



                        if (!isset($upload_response['data'])) {

                            echo "Error uploading file: $file_name <br>";

                        }

                    }

                }

                if (!empty($_FILES['transcript']['name'])) {

                    $file_name = $_FILES['transcript']['name'];

                    $file_path = $_FILES['transcript']['tmp_name'];

                    $file_type = $_FILES['transcript']['type'];



                    if (is_uploaded_file($file_path)) {

                        $file_data = [

                            'file_name' => 'transcript.jpg',

                            'file_path' => $file_path,

                            'file_type' => $file_type

                        ];



                        $upload_response = (new Zoho())->request_att("Leads/$lead_id/Attachments", 'POST', $file_data);



                        if (!isset($upload_response['data'])) {

                            echo "Error uploading file: $file_name <br>";

                        }

                    }

                }

                echo "Thank you for reaching out! We have received your message and will get back to you as soon as possible. If your inquiry is urgent, please feel free to contact us directly. We appreciate your interest and look forward to assisting you! ";

            } else {

                echo "Error creating lead!";

            }

        }

        //$prog_id = isset($_GET['prog_id']) && $_GET['prog_id'] != 0 ? intval($_GET['prog_id']) : '';
        $uni_id = get_the_ID();
        $permalink = get_the_permalink($uni_id);
        $title = get_the_title($uni_id);
        ob_start();

        if ($uni_id){

            /*$program= get_post($prog_id);

            $uni_id=get_post_meta($prog_id, 'zh_university', true);

            $university = get_post($uni_id);

            $programs=[

                'pro_id'=>get_post_meta($prog_id, 'zoho_product_id', true),

                'uni_id'=>get_post_meta($uni_id, 'zoho_account_id', true),

                'title'=>$program->post_title,

                'link'=>$program->guid,

                'fee' => get_post_meta($prog_id, 'Official_Tuition', true),

                'duration' => get_post_meta($prog_id, 'Study_Years', true),

                'pro_country' => (function() use ($prog_id) {
                    $terms = get_the_terms($prog_id, 'sit-country');
                    return (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
                })(),

                'country' => (function() use ($uni_id) {
                    $terms = get_the_terms($uni_id, 'sit-country');
                    return (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
                })(),

                'city' => (function() use ($uni_id) {
                    $terms = get_the_terms($uni_id, 'sit-city');
                    return (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
                })(),

                'description' => get_post_meta($prog_id, 'Description', true),

                'uni_description' => get_post_meta($uni_id, 'Description', true),

                'ranking' => get_post_meta($uni_id, 'QS_Rank', true),

                'uni_title' => $university->post_title,

                'uni_link' => $university->guid,

                'image_url'=>!empty(get_post_meta($uni_id, 'uni_image', true))  ?

                    esc_url(get_post_meta($uni_id, 'uni_image', true))

                    :'https://placehold.co/714x340?text=University',

                'Year_Founded'=>get_post_meta($uni_id, 'Year_Founded', true),

                'total_students' => get_post_meta($uni_id, 'Number_Of_Students', true),

            ];*/

            $uni = [
                "title"=> $title,
                "link"=>$permalink,
                "uni_id"=>$uni_id,
            ];


            Template::render('shortcodes/consultation',['uni_details'=>$uni]);

        }

        return ob_get_clean();

    }

}