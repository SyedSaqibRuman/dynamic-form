<?php
defined('ABSPATH') || exit;

/**
 * Admin – List & Export Form Entries
 */

/* =====================================================
 * Permission Check
 * ===================================================== */
if (!current_user_can('manage_options')) {
  wp_die(__('You do not have permission to access this page.', 'dynamic-form'));
}

/* =====================================================
 * Handle Export (JSON / CSV)
 * ===================================================== */
if (isset($_GET['export'])) {

  $export  = sanitize_text_field($_GET['export']);
  $entries = DF_FormRepository::get_all_entries(10000);

  // -------- JSON EXPORT --------
  if ($export === 'json') {
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=df-entries.json');

    echo wp_json_encode($entries, JSON_PRETTY_PRINT);
    exit;
  }

  // -------- CSV (EXCEL) EXPORT --------
  if ($export === 'csv') {
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=df-entries.csv');

    $output = fopen('php://output', 'w');

    // CSV Header
    fputcsv($output, [
      'Entry ID',
      'Form ID',
      'Field',
      'Value',
      'IP Address',
      'User Agent',
      'Date'
    ]);

    foreach ($entries as $entry) {
      $data = json_decode($entry['data'], true) ?: [];

      foreach ($data as $field => $value) {
        fputcsv($output, [
          $entry['id'],
          $entry['form_id'],
          $field,
          is_array($value) ? implode(', ', $value) : $value,
          $entry['ip_address'],
          $entry['user_agent'],
          $entry['created_at'],
        ]);
      }
    }

    fclose($output);
    exit;
  }
}

/* =====================================================
 * Pagination Setup
 * ===================================================== */
$per_page = 20;

$current_page = isset($_GET['paged'])
  ? max(1, absint($_GET['paged']))
  : 1;

$offset = ($current_page - 1) * $per_page;

$total_entries = DF_FormRepository::count_entries();
$total_pages   = (int) ceil($total_entries / $per_page);

$entries = DF_FormRepository::get_all_entries($per_page, $offset);
?>

<div class="wrap">
  <h1><?php esc_html_e('Form Submissions', 'dynamic-form'); ?></h1>

  <div style="margin: 15px 0;">
    <a
      href="<?php echo esc_url(admin_url('admin.php?page=df-entries&export=json')); ?>"
      class="button button-secondary">
      <?php esc_html_e('Export JSON', 'dynamic-form'); ?>
    </a>

    <a
      href="<?php echo esc_url(admin_url('admin.php?page=df-entries&export=csv')); ?>"
      class="button button-secondary">
      <?php esc_html_e('Export Excel', 'dynamic-form'); ?>
    </a>
  </div>

  <table class="widefat fixed striped">
    <thead>
      <tr>
        <th><?php esc_html_e('ID', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Form ID', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Submission Data', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('IP Address', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Date', 'dynamic-form'); ?></th>
      </tr>
    </thead>

    <tbody>
      <?php if (empty($entries)) : ?>
        <tr>
          <td colspan="5"><?php esc_html_e('No entries found.', 'dynamic-form'); ?></td>
        </tr>
      <?php else : ?>
        <?php foreach ($entries as $entry) : ?>
          <tr>
            <td><?php echo esc_html($entry['id']); ?></td>
            <td><?php echo esc_html($entry['form_id']); ?></td>
            <td>
              <pre style="white-space: pre-wrap; max-width: 500px;">
<?php
          echo esc_html(
            wp_json_encode(
              json_decode($entry['data'], true),
              JSON_PRETTY_PRINT
            )
          );
?>
              </pre>
            </td>
            <td><?php echo esc_html($entry['ip_address']); ?></td>
            <td><?php echo esc_html($entry['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($total_pages > 1) : ?>
    <div class="tablenav bottom">
      <div class="tablenav-pages">
        <span class="displaying-num">
          <?php echo esc_html($total_entries); ?> items
        </span>

        <span class="pagination-links">
          <?php
          $base_url = remove_query_arg(['paged'], $_SERVER['REQUEST_URI']);

          // Previous
          if ($current_page > 1) {
            printf(
              '<a class="prev-page button" href="%s">&lsaquo;</a>',
              esc_url(add_query_arg('paged', $current_page - 1, $base_url))
            );
          } else {
            echo '<span class="tablenav-pages-navspan button disabled">&lsaquo;</span>';
          }

          // Page indicator
          echo '<span class="paging-input">';
          echo esc_html($current_page) . ' of ' . esc_html($total_pages);
          echo '</span>';

          // Next
          if ($current_page < $total_pages) {
            printf(
              '<a class="next-page button" href="%s">&rsaquo;</a>',
              esc_url(add_query_arg('paged', $current_page + 1, $base_url))
            );
          } else {
            echo '<span class="tablenav-pages-navspan button disabled">&rsaquo;</span>';
          }
          ?>
        </span>
      </div>
    </div>
  <?php endif; ?>
</div>
