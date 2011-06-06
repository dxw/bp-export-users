<?php
/*
 * Plugin Name: BP Export Users
 * Description: Allows exporting of all user data from WordPress and BuddyPress to CSV.
 * Author: dxw
 * Author URI: http://dxw.net/
 */

# http://www.php.net/manual/en/function.fputcsv.php#87120
#TODO: doesn't handle newlines
function fputcsv2 ($fh, array $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false) {
  $delimiter_esc = preg_quote($delimiter, '/');
  $enclosure_esc = preg_quote($enclosure, '/');

  $output = array();
  foreach ($fields as $field) {
    if ($field === null && $mysql_null) {
      $output[] = 'NULL';
      continue;
    }

    $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
  }

  fwrite($fh, join($delimiter, $output) . "\n");
}

# http://www.php.net/manual/en/function.fputcsv.php#103987
function array_to_CSV($data)
{
  $outstream = fopen("php://temp", 'r+');
  fputcsv2($outstream, $data, ',', '"');
  rewind($outstream);
  $csv = fgets($outstream);
  fclose($outstream);
  return $csv;
}


class BP_Export_Users {

  function __construct() {
    add_action('admin_init', array($this,'admin_init'));
    add_action('admin_menu', array($this,'admin_menu'));

    $this->wp_fields = array(
      'ID',
      'user_login',
      #'user_pass',
      'user_nicename',
      'user_email',
      'user_url',
      'user_registered',
      #'user_activation_key',
      'user_status',
      'display_name',
      'spam',
      'deleted');

    $this->bp_fields = array(
      'Name',
      'Telephone',
      'Job Title',
      'Organisation',
      'Region',
      'Primary Discipline',
      'Grade',
      'A bit about you',
      'twitter',
      'flickr');

    // The provided list of BuddyPress fields is probably site-specific, so...
    $this->wp_fields = apply_filters('bp_export_users_wp_fields', $this->wp_fields);
    $this->bp_fields = apply_filters('bp_export_users_bp_fields', $this->bp_fields);
  }

  function admin_menu() {
    add_submenu_page('tools.php', 'Export Users', 'Export Users', 'export', 'export-users', array($this, 'page'));
  }

  function admin_init() {
    if(!empty($_GET['page']) && $_GET['page'] === 'export-users' && !empty($_GET['export']))
      $this->export();
  }

  function sanitize($value) {
    $value = str_replace("\r", '', $value);
    $value = str_replace("\n", '', $value);
    $value = str_replace("\t", '', $value);
    return $value;
  }

  function get_csv() {
    $csv = array_to_CSV(array_merge($this->wp_fields, $this->bp_fields));

    foreach (get_users(array('blog_id' => 0)) as $user) {
      $row = array();
      foreach ($this->wp_fields as $field) {
        $row[$field] = $this->sanitize($user->{$field});
      }

      $bp_data = BP_XProfile_ProfileData::get_all_for_user($user->ID);
      foreach ($this->bp_fields as $field) {
        $value = $bp_data[$field];

        if (is_array($value))
          $value = $value['field_data'];

        $value = $this->sanitize($value);

        $row[$field] = $value;
      }

      $csv .= array_to_CSV($row);

      // More data (?):
      #print_r(get_userdata( $user->ID ));
    }

    return $csv;
  }

  function export() {
    $basename = ('buddypress-users_'.strftime('%Y-%m-%d'));

    header('Pragma: public');
    header('Cache-control: max-age=0');
    header("Content-Type: text/csv; charset=utf8");
    header('Content-Disposition: attachment; filename='.$basename.'.csv');

    echo $this->get_csv();
    die();
  }

  function page() {
?>
<div class="wrap">
  <div id="icon-tools" class="icon32"><br></div>
  <h2>Export Users</h2>

  <p>When you click the button below WordPress will create a CSV file for you to save to your computer.</p>

  <p class="submit"><a class="button-secondary" href="<?php echo get_admin_url(null, 'tools.php?page=export-users&export=1') ?>">Download Export file</a></p>
</div>
<?php
  }
}

new BP_Export_Users;
