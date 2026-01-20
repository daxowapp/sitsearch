<?php
$degree_term = get_term($degreeid, 'sit-degree');
$country_term = get_term($countryid, 'sit-country');
$speciality_term = get_term($specialityid, 'sit-speciality');

// Check if terms are valid (not WP_Error and not empty)
$degree_valid = (!is_wp_error($degree_term) && $degree_term);
$country_valid = (!is_wp_error($country_term) && $country_term);
$speciality_valid = (!is_wp_error($speciality_term) && $speciality_term);

if ($speciality_valid && $degree_valid && $country_valid) {
    $heading = $degree_term->name . ' ' . $speciality_term->name . ' Courses ' . $country_term->name;
} else {
    $heading = "Search For Course";
}
$pro_to=0;
foreach ($terms as $term) {
    $pro_to = $pro_to + $term['count'];
}

// Configure which filters to show for this page (Campus-specific results)
$filter_config = [
    'degree' => false,     // Hide degree filter
    'duration' => true,    // Show duration filter
    'language' => true,    // Show language filter
    'price' => false,      // Hide price filter
    'university' => false, // Hide university filter
    'scholarship' => true  // Show scholarship filter
];

// Prepare filter data
$filter_data = [
    'degrees' => [],
    'universities' => []
];

// Set page-specific variables
$results_count = count($programs);
$search_value = isset($_GET['search']) ? $_GET['search'] : '';
$heading = $disstr; // Use existing campus description
?>

<!-- Include Shared Filter Styles -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../shared/filter-styles.css">

<?php
// Include Shared Header
$results_count = $pro_to;
include plugin_dir_path(__FILE__) . '../shared/results-header.php';
?>
<!-- Main Container with Sidebar Layout -->
<div class="filter-results-container">
    <?php
    // Include Shared Sidebar
    include plugin_dir_path(__FILE__) . '../shared/filter-sidebar.php';
    ?>

        <!-- Results Main Content -->
        <div class="results-main-content">
            <div class="single-campus-content">
                <h4>All Programs</h4>
        
        <!-- GRID VIEW: Default view -->
        <div class="all-faculties-program" id="programsGridContainer">
            <?php
            foreach ($programs as $university) {
                \SIT\Search\Services\Template::render('shortcodes/program-box-uni', ['program' => $university]);
            }
            ?>
        </div>
        
        <!-- LIST VIEW: Compact mobile-optimized view -->
        <div class="all-faculties-program-list" id="programsListContainer" style="display: none;">
            <?php
            foreach ($programs as $university) {
                // Use the same program data structure as your grid view
                $program = $university;
                ?>
                <div class="program-list-item">
                    <div class="program-list-image">
                        <?php if (!empty($program['image_url'])): ?>
                            <img src="<?php echo $program['image_url']; ?>" alt="<?php echo $program['title']; ?>">
                        <?php else: ?>
                            <div class="program-list-placeholder">🏫</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="program-list-content">
                        <div class="program-list-info">
                            <h3 class="program-list-title"><?php echo $program['title']; ?></h3>
                            <p class="program-list-university"><?php echo $program['uni_title']; ?></p>
                            
                            <div class="program-list-details">
                                <span class="program-list-detail">
                                    🕒 <?php echo $program['duration']; ?>
                                </span>
                                <span class="program-list-detail">
                                    🌐 <?php 
                                    // Extract language from title if it's in parentheses at the end
                                    if (preg_match('/\(([^)]+)\)$/', $program['title'], $matches)) {
                                        echo $matches[1];
                                    } else {
                                        // Fallback to a default or extract from other fields
                                        echo 'English'; // or extract from other program data
                                    }
                                    ?>
                                </span>
                                <span class="program-list-detail">
                                    📍 <?php echo $program['country']; ?>
                                </span>
                                <?php if (!empty($program['ranking'])): ?>
                                <span class="program-list-detail">
                                    ⭐ Ranking: <?php echo $program['ranking']; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="program-list-right">
                            <div class="program-list-fee">
                                <?php if (!empty($program['discounted_fee'])): ?>
                                    <span class="program-list-original-fee"><?php echo $program['fee']; ?> USD</span>
                                    <span class="program-list-discounted-fee"><?php echo $program['discounted_fee']; ?> USD</span>
                                <?php else: ?>
                                    <span class="program-list-current-fee"><?php echo $program['fee']; ?> USD</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="program-list-actions">
                                <?php 
                                // Simple and safe approach
                                $program_link = isset($program['link']) ? $program['link'] : '#';
                                
                                // Apply URL logic
                                $uni_id = $program['uni_id'] ?? '';
                                if ($uni_id) {
                                    $apply_url = \SIT\Search\Services\URLHelper::apply($uni_id);
                                }
                                ?>
                                <a href="<?php echo esc_url($program_link); ?>" class="program-list-btn program-list-btn-primary">View Details</a>
                                <a href="<?php echo esc_url($apply_url); ?>" class="program-list-btn program-list-btn-outline" target="_blank">Apply</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
        }
        ?>
    </div>
        </div>
    </div>
</div>

<!-- Export Popup -->
<div class="export-overlay" id="exportModal">
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <button onclick="downloadPDF()" class="print-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer h-4 w-4"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path><rect x="6" y="14" width="12" height="8" rx="1"></rect></svg> Print/Save PDF</button>
                <p>Total Programs:<?= count($programs); ?></p>
            </div>
            <button class="close-export" onclick="closeExportPopup()">×</button>
        </div>

        <p><?= $disstr ?></p>

        <h3>Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Deadline</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($programs as $program) {
                if(!empty($program['discounted_fee'])){
                    $fee='<span>'.$program['fee'].' USD</span>'.$program['discounted_fee'].' USD';
                }
                else{
                    $fee=$program['fee'].' USD';
                }
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $program['duration'] ?></td>
                    <td>English</td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <h3>Program Details</h3>
        <?php
        foreach ($programs as $program) {
            ?>
            <div class="program-card">
                <div class="university-image">
                    <img src="<?= $program['image_url'] ?>" alt="">
                </div>
                <div class="program-detail">
                    <div class="program-title"><?= $program['title'] ?></div>
                    <div class="uni-name"><?= $program['uni_title'] ?></div>
                    <div class="program-info-grid">
                        <div class="info-item">
                            <span class="icon">🕒</span>
                            <span><?= $program['duration'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">🌐</span>
                            <span>English</span>
                        </div>
                        <div class="info-item">
                            <span class="icon">📍</span>
                            <span><?= $program['country'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">💲</span>
                            <span><?= $program['fee'] ?> USD</span>
                        </div>
                        <div class="info-item">
                            <span class="icon">💳</span>
                            <span>Rankings: <?= $program['ranking'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">📅</span>
                            <span>Students: <?= $program['students'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <!-- Repeat .program-card blocks for other programs -->

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p>This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>
<div id="expoting-download" >
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <p>Total Programs:<?= count($programs); ?></p>
            </div>
        </div>

        <p><?= $disstr ?></p>

        <h3>Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Deadline</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($programs as $program) {
                if(!empty($program['discounted_fee'])){
                    $fee='<span style=" text-decoration: line-through;display: block;">'.$program['fee'].' USD</span>'.$program['discounted_fee'].' USD';
                }
                else{
                    $fee=$program['fee'].' USD';
                }
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $program['duration'] ?></td>
                    <td>English</td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p>This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>
<script>
    function closeExportPopup() {
        document.getElementById("exportModal").style.display = "none";
    }

    function openExportPopup() {
        document.getElementById("exportModal").style.display = "flex";
    }
</script>
<script>
    function downloadPDF() {
        const originalElement = document.getElementById("expoting-download");

        // Clone the original element
        const clone = originalElement.cloneNode(true);

        // Remove unwanted elements from clone (contact-info, footer, and the original table)
        const contactInfo = clone.querySelector('.contact-info');
        const footer = clone.querySelector('.footer-popup');
        const table = clone.querySelector("table");

        if (contactInfo) contactInfo.remove();
        if (footer) footer.remove();
        if (table) table.remove(); // Remove the original table from the clone

        // --- Get content before the table (for first page only) ---
        const headerContent = document.createElement("div");
        let reachedTable = false;
        Array.from(clone.childNodes).forEach(node => {
            if (!reachedTable) {
                headerContent.appendChild(node.cloneNode(true));
            }
        });

        // Reattach the table we are going to use (to paginate it later)
        const newTable = document.createElement("table");
        newTable.style.width = "100%";
        newTable.style.borderCollapse = "collapse";

        // Get the header (thead) from the original table
        const thead = table.querySelector("thead")?.cloneNode(true);
        if (thead) newTable.appendChild(thead.cloneNode(true));

        const rows = Array.from(table.querySelectorAll("tbody tr"));

        // Wrapper to hold all generated pages
        const wrapper = document.createElement("div");

        // Split rows in chunks of 4 and build pages
        for (let i = 0; i < rows.length; i += 6) {
            const pageDiv = document.createElement("div");
            pageDiv.style.pageBreakAfter = "always";

            // Add header content for the first page
            if (i === 0) {
                pageDiv.appendChild(headerContent.cloneNode(true));
            }

            // Create new table and append up to 4 rows
            const pageTable = document.createElement("table");
            pageTable.style.width = "100%";
            pageTable.style.borderCollapse = "collapse";
            if (thead) pageTable.appendChild(thead.cloneNode(true));

            const tbody = document.createElement("tbody");
            for (let j = i; j < i + 6 && j < rows.length; j++) {
                tbody.appendChild(rows[j].cloneNode(true));
            }
            pageTable.appendChild(tbody);
            pageDiv.appendChild(pageTable);

            wrapper.appendChild(pageDiv);
        }

        // Final page with contact info and footer
        const finalPage = document.createElement("div");
        finalPage.style.padding = "20px";
        finalPage.innerHTML = `
        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup" style="margin-top: 40px; text-align: center;">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p style="font-style: italic; font-size: 12px;">This document was generated for informational purposes only.</p>
        </div>
    `;
        wrapper.appendChild(finalPage);

        // Generate the PDF
        const options = {
            margin: 10,
            filename: 'Academic_Programs_Turkey.pdf',
            image: { type: 'png', quality: 1 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                letterRendering: true
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().from(wrapper).set(options).save().catch(function (error) {
            console.error("Error generating PDF:", error);
        });
    }

            if (contactInfo) contactInfo.remove();
            if (footer) footer.remove();
            if (table) table.remove(); // Remove the original table from the clone

            // --- Get content before the table (for first page only) ---
            const headerContent = document.createElement("div");
            let reachedTable = false;
            Array.from(clone.childNodes).forEach(node => {
                if (!reachedTable) {
                    headerContent.appendChild(node.cloneNode(true));
                }
            });

            // Reattach the table we are going to use (to paginate it later)
            const newTable = document.createElement("table");
            newTable.style.width = "100%";
            newTable.style.borderCollapse = "collapse";

            // Get the header (thead) from the original table
            const thead = table.querySelector("thead")?.cloneNode(true);
            if (thead) newTable.appendChild(thead.cloneNode(true));

            const rows = Array.from(table.querySelectorAll("tbody tr"));

            // Wrapper to hold all generated pages
            const wrapper = document.createElement("div");

            // Split rows in chunks of 4 and build pages
            for (let i = 0; i < rows.length; i += 6) {
                const pageDiv = document.createElement("div");
                pageDiv.style.pageBreakAfter = "always";

                // Add header content for the first page
                if (i === 0) {
                    pageDiv.appendChild(headerContent.cloneNode(true));
                }

                // Create new table and append up to 4 rows
                const pageTable = document.createElement("table");
                pageTable.style.width = "100%";
                pageTable.style.borderCollapse = "collapse";
                if (thead) pageTable.appendChild(thead.cloneNode(true));

                const tbody = document.createElement("tbody");
                for (let j = i; j < i + 6 && j < rows.length; j++) {
                    tbody.appendChild(rows[j].cloneNode(true));
                }
                pageTable.appendChild(tbody);
                pageDiv.appendChild(pageTable);

                wrapper.appendChild(pageDiv);
            }

            // Final page with contact info and footer
            const finalPage = document.createElement("div");
            finalPage.style.padding = "20px";
            finalPage.innerHTML = `
            <div class="contact-info">
                <h4>Contact Information</h4>
                <p>
                    For more information about these programs or assistance with your application, please contact our support team.<br>
                    Email: support@studyinturkey.com<br>
                    Website: www.studyinturkey.com
                </p>
            </div>
            <div class="footer-popup" style="margin-top: 40px; text-align: center;">
                <p> 2025 Study in Turkey. All rights reserved.</p>
                <p style="font-style: italic; font-size: 12px;">This document was generated for informational purposes only.</p>
            </div>
        `;
            wrapper.appendChild(finalPage);

            // Generate the PDF
            const options = {
                margin: 10,
                filename: 'Academic_Programs_Turkey.pdf',
                image: { type: 'png', quality: 1 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    letterRendering: true
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().from(wrapper).set(options).save().catch(function (error) {
                console.error("Error generating PDF:", error);
            });
        }




    </script>


    <style>
        /* View Toggle Styles - Same as other pages */
        .view-toggle {
            display: flex;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 12px;
        }

        .view-toggle button {
            padding: 10px 12px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
            color: #6c757d;
        }

        .view-toggle button.active {
            background: #E20A17;
            color: white;
        }

        .view-toggle button:hover:not(.active) {
            background: #e9ecef;
            color: #495057;
        }

        /* List View Styles - Same as program-archive.html.php */
        .all-faculties-program-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
        }

        .program-list-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .program-list-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #E20A17;
        }

        .program-list-image {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .program-list-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .program-list-placeholder {
            font-size: 24px;
            color: #6c757d;
        }

        .program-list-content {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .program-list-info {
            flex: 1;
        }

        .program-list-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        .program-list-university {
            color: #6c757d;
            font-size: 14px;
            margin: 0 0 12px 0;
        }

        .program-list-details {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .program-list-detail {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: #6c757d;
        }

        .program-list-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 16px;
            text-align: right;
        }

        .program-list-fee {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .program-list-original-fee {
            font-size: 14px;
            color: #6c757d;
            text-decoration: line-through;
            margin-bottom: 2px;
        }

        .program-list-discounted-fee,
        .program-list-current-fee {
            font-size: 18px;
            font-weight: 600;
            color: #E20A17;
        }

        .program-list-actions {
            display: flex;
            gap: 8px;
        }

        .program-list-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
        }

        .program-list-btn-primary {
            background: #E20A17;
            color: white;
            border: 1px solid #E20A17;
        }

        .program-list-btn-primary:hover {
            background: #B8080F;
            border-color: #B8080F;
            transform: translateY(-1px);
        }

        .program-list-btn-outline {
            background: transparent;
            color: #E20A17;
            border: 1px solid #E20A17;
        }

        .program-list-btn-outline:hover {
            background: #E20A17;
            color: white;
            transform: translateY(-1px);
        }

        /* Mobile Responsive - Same compact design */
        @media (max-width: 768px) {
            .view-toggle {
                order: -1;
                width: 100%;
                justify-content: center;
                margin-right: 0;
                margin-bottom: 12px;
            }
            
            .all-faculties-program-list {
                gap: 12px;
                padding: 0 4px;
            }
            
            .program-list-item {
                flex-direction: row;
                align-items: center;
                text-align: left;
                gap: 0;
                padding: 12px 16px;
                min-height: auto;
            }
            
            .program-list-image {
                display: none;
            }
            
            .program-list-content {
                flex-direction: row;
                gap: 12px;
                width: 100%;
                justify-content: space-between;
                align-items: center;
            }
            
            .program-list-info {
                text-align: left;
                flex: 1;
                min-width: 0;
            }
            
            .program-list-title {
                font-size: 15px;
                margin-bottom: 4px;
                line-height: 1.2;
            }
            
            .program-list-university {
                font-size: 12px;
                margin-bottom: 6px;
                color: #666;
            }
            
            .program-list-right {
                align-items: flex-end;
                text-align: right;
                gap: 8px;
                min-width: 120px;
                flex-shrink: 0;
            }
            
            .program-list-details {
                justify-content: flex-start;
                gap: 6px;
                flex-wrap: wrap;
                margin-top: 2px;
            }
            
            .program-list-detail {
                font-size: 11px;
                padding: 2px 6px;
                background: #f0f0f0;
                border-radius: 3px;
                white-space: nowrap;
            }
            
            .program-list-fee {
                margin-bottom: 8px;
            }
            
            .program-list-original-fee {
                font-size: 11px;
            }
            
            .program-list-discounted-fee,
            .program-list-current-fee {
                font-size: 14px;
                font-weight: 600;
            }
            
            .program-list-actions {
                width: 100%;
                flex-direction: column;
                gap: 4px;
            }
            
            .program-list-btn {
                width: 100%;
                padding: 6px 10px;
                font-size: 11px;
                min-width: 0;
                white-space: nowrap;
            }
        }

        @media (max-width: 480px) {
            .all-faculties-program-list {
                gap: 8px;
                padding: 0 2px;
            }
            
            .program-list-item {
                padding: 10px 12px;
                gap: 0;
            }
            
            .program-list-btn {
                padding: 5px 8px;
                font-size: 10px;
            }
        }
    </style>

    <script>
        // View toggle functionality for single-campus page
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.view-btn');
            const gridContainer = document.getElementById('programsGridContainer');
            const listContainer = document.getElementById('programsListContainer');
            
            if (!gridContainer || !listContainer) {
                return;
            }
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                  
                    const currentView = this.dataset.view;
                    
                    if (currentView === 'grid') {
                        gridContainer.style.display = '';
                        listContainer.style.display = 'none';
                    } else if (currentView === 'list') {
                        gridContainer.style.display = 'none';
                        listContainer.style.display = 'flex';
                    }
                    
                    localStorage.setItem('programView', currentView);
                });
            });
            
            // Restore saved view preference
            const savedView = localStorage.getItem('programView');
            if (savedView && savedView === 'list') {
                const listButton = document.querySelector('[data-view="list"]');
                if (listButton) {
                    listButton.click();
                }
            }
        });

    </script>

    <!-- Include Shared Filter Scripts -->
    <script src="<?= plugin_dir_url(__FILE__) ?>../shared/filter-scripts.js"></script>
