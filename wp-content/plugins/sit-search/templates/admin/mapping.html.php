<?php
/**
 * Mapping Template
 *
 * @package SIT\Search
 */

$zoho = new SIT\Search\Services\Zoho();

$university_fields = $zoho->get_fields('Accounts');
$program_fields = $zoho->get_fields('Products');

$university_enabled_fields = get_option('university_enabled_fields', []);
$program_enabled_fields = get_option('program_enabled_fields', []);

if (isset($_POST['sync_fields'])) {
    $university_enabled_fields = $_POST['university_enabled_fields'];
    $program_enabled_fields = $_POST['program_enabled_fields'];

    update_option('university_enabled_fields', $university_enabled_fields);
    update_option('program_enabled_fields', $program_enabled_fields);
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Fields Mapping</h1>
    <a href="https://search.studyinturkiye.com/wp-admin/post-new.php" class="page-title-action">Refresh Fields</a>
    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tbody>
            <tr class="user-rich-editing-wrap">
                <th scope="row">University Fields</th>
                <td class="flex-fields">

                    <?php
                    foreach ($university_fields as $field) {
                        ?>
                        <label for="<?= $field['field_name'] ?>">
                            <input name="university_enabled_fields[]" type="checkbox" id="<?= $field['field_name'] ?>" value="<?= $field['field_name'] ?>"
                                <?= $university_enabled_fields ? checked(in_array($field['field_name'], $university_enabled_fields), true) : '' ?>>
                            <?php echo $field['field_label']; ?>
                        </label>
                        <span>|</span>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr class="user-rich-editing-wrap">
                <th scope="row">Program Fields</th>
                <td class="flex-fields">

                    <?php
                    foreach ($program_fields as $field) {
                        ?>
                        <label for="<?= $field['field_name'] ?>">
                            <input name="program_enabled_fields[]" type="checkbox" id="<?= $field['field_name'] ?>" value="<?= $field['field_name'] ?>"
                                <?= !empty($program_enabled_fields) ? checked(in_array($field['field_name'], $program_enabled_fields), true) : '' ?> />
                            <?php echo $field['field_label']; ?>
                        </label>
                        <span>|</span>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="sync_fields" id="submit" class="button button-primary" value="Sync Fields">
        </p>
    </form>
</div>